<?php

namespace Tests\Feature;

use App\Livewire\Calendrier;
use App\Livewire\Cotisation;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Tontine;
use App\Models\TontineDay;
use App\Models\TontinePayment;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class CotisationCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_records_a_daily_payment_and_receives_confirmation(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');

        $component = $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors()->assertSet('amount', '')->assertDispatched('payment-updated');

        $this->assertDatabaseHas('tontine_payments', ['tenant_id' => $user->tenant_id, 'tontine_id' => $tontine->id, 'amount' => 50000, 'recorded_by' => $user->id, 'voided_at' => null]);
        $this->assertDatabaseHas('tontine_days', ['tenant_id' => $user->tenant_id, 'tontine_id' => $tontine->id, 'amount' => 50000]);
        $component->assertSee('La cotisation a été enregistrée avec succès.')->assertSee('Opération réussie');
    }

    public function test_same_day_cannot_be_paid_twice(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors();

        $this->paymentForm($user, $tontine)->call('save')->assertHasErrors('paymentDate')->assertSee('Une cotisation est déjà enregistrée');

        $this->assertDatabaseCount('tontine_payments', 1);
    }

    public function test_amount_must_match_the_daily_stake(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');

        $this->paymentForm($user, $tontine)->set('amount', '25000')->call('save')->assertHasErrors('amount');

        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_payment_date_must_be_inside_the_cycle(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');

        $this->paymentForm($user, $tontine)->set('paymentDate', '2026-09-30')->call('save')->assertHasErrors('paymentDate');

        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_other_tenants_tontine_cannot_receive_a_payment(): void
    {
        [$user] = $this->createTontine('alpha');
        [, $other] = $this->createTontine('beta');

        $this->paymentForm($user, $other)->call('save')->assertHasErrors(['customerId', 'tontineId']);

        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_future_payment_is_rejected(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');

        $this->paymentForm($user, $tontine)->set('paymentDate', now()->addDay()->toDateString())
            ->call('save')->assertHasErrors(['paymentDate' => 'before_or_equal']);

        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_tontine_must_belong_to_the_selected_client(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $otherClient = Customer::query()->create(['tenant_id' => $user->tenant_id, 'reference' => 'CL-OTHER', 'name' => 'Autre cliente', 'status' => 'Actif']);

        $this->paymentForm($user, $tontine)->set('customerId', (string) $otherClient->id)
            ->set('tontineId', (string) $tontine->id)->set('amount', '50000')
            ->call('save')->assertHasErrors(['tontineId' => 'exists']);

        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_closed_tontine_cannot_receive_a_payment(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $tontine->update(['status' => 'closed']);

        $this->paymentForm($user, $tontine)->call('save')->assertHasErrors('tontineId');

        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_cancelled_payment_is_reactivated_instead_of_duplicated(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors();
        TontinePayment::query()->sole()->update(['voided_at' => now()]);

        $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors();

        $this->assertDatabaseCount('tontine_payments', 1);
        $this->assertNull(TontinePayment::query()->sole()->voided_at);
    }

    public function test_calendar_link_prefills_client_day_and_amount_without_recording_payment(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        Livewire::actingAs($user)->withQueryParams(['tontine' => (string) $tontine->id, 'day' => '2026-10-15'])
            ->test(Cotisation::class)->assertSet('customerId', (string) $tontine->customer_id)
            ->assertSet('tontineId', (string) $tontine->id)->assertSet('paymentDate', '2026-10-15')->assertSet('amount', '50000');

        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_calendar_link_rejects_another_tenant_and_future_days(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        [, $other] = $this->createTontine('beta');
        $this->actingAs($user)->get(route('cotisations', ['tontine' => $other->id, 'day' => '2026-10-15']))->assertNotFound();
        $this->actingAs($user)->get(route('cotisations', ['tontine' => $tontine->id, 'day' => now()->addDay()->toDateString()]))->assertUnprocessable();
    }

    public function test_late_payment_covers_old_day_but_counts_as_received_today(): void
    {
        $this->travelTo(Carbon::parse('2026-10-23 10:00:00'));
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors();

        $payment = TontinePayment::query()->with('day')->sole();
        $this->assertSame('2026-10-15', $payment->day->due_date->toDateString());
        $this->assertSame('2026-10-23', $payment->payment_date->toDateString());
        Livewire::actingAs($user)->test(Calendrier::class)
            ->assertViewHas('paidTodayAmount', fn ($amount) => (int) $amount === 50000)
            ->assertViewHas('calendarDays', fn ($days) => $days->firstWhere('date', '2026-10-15')['is_paid']);
    }

    public function test_received_date_rejects_future_date_and_date_before_due_day(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->set('receivedDate', now()->addDay()->toDateString())
            ->call('save')->assertHasErrors(['receivedDate' => 'before_or_equal']);
        $this->paymentForm($user, $tontine)->set('receivedDate', '2026-10-14')
            ->call('save')->assertHasErrors(['receivedDate' => 'after_or_equal']);
        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_cancellation_requires_confirmation_and_reason_then_preserves_history_on_reactivation(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors();
        $payment = TontinePayment::query()->sole();
        $page = Livewire::actingAs($user)->test(Cotisation::class)->call('confirmCancellation', $payment->id);
        $this->assertNull($payment->fresh()->voided_at);
        $page->call('cancelPayment')->assertHasErrors('cancellationReason');
        $this->assertNull($payment->fresh()->voided_at);
        $page->set('cancellationReason', 'Erreur de saisie')->call('cancelPayment')->assertHasNoErrors()->assertSet('cancellingId', null);
        $this->assertNotNull($payment->fresh()->voided_at);
        $this->assertSame('Erreur de saisie', $payment->fresh()->history[1]['reason']);
        $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors();
        $payment->refresh();
        $this->assertNull($payment->voided_at);
        $this->assertSame(['recorded', 'cancelled', 'recorded'], array_column($payment->history, 'action'));
        $this->assertSame($user->id, $payment->history[1]['user_id']);
        $this->assertDatabaseCount('tontine_payments', 1);
    }

    public function test_cancellation_can_be_dismissed_without_changing_payment(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->call('save');
        $payment = TontinePayment::query()->sole();
        Livewire::actingAs($user)->test(Cotisation::class)->call('confirmCancellation', $payment->id)
            ->call('dismissCancellation')->assertSet('cancellingId', null);
        $this->assertNull($payment->fresh()->voided_at);
        $this->assertCount(1, $payment->fresh()->history);
    }

    public function test_foreign_payment_cannot_be_cancelled_and_is_hidden(): void
    {
        [$owner] = $this->createTontine('alpha');
        [$otherOwner, $other] = $this->createTontine('beta');
        $this->paymentForm($otherOwner, $other)->call('save')->assertHasNoErrors();
        $payment = TontinePayment::query()->sole();
        Livewire::actingAs($owner)->test(Cotisation::class)->assertDontSee('Cliente beta')
            ->call('confirmCancellation', $payment->id)->assertNotFound();
        $this->assertDatabaseHas('tontine_payments', ['id' => $payment->id, 'voided_at' => null]);
    }

    public function test_existing_day_amount_survives_daily_stake_changes(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->call('save');
        $payment = TontinePayment::query()->sole();
        Livewire::actingAs($user)->test(Cotisation::class)->call('confirmCancellation', $payment->id)
            ->set('cancellationReason', 'Erreur de saisie')->call('cancelPayment');
        $tontine->update(['daily_amount' => 75000]);
        $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors();
        $this->assertSame(50000, (int) $payment->fresh()->amount);
    }

    public function test_multiple_days_are_saved_together_with_total_flash_and_history(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->set('paymentEndDate', '2026-10-17')
            ->assertSet('amount', '150000')->assertSee('3 journée(s)')
            ->call('save')->assertHasNoErrors()->assertSet('paymentEndDate', '')->assertDispatched('payment-updated')
            ->assertSee('Les 3 journées de cotisation ont été enregistrées avec succès.');
        $this->assertDatabaseCount('tontine_days', 3);
        $this->assertDatabaseCount('tontine_payments', 3);
        $this->assertSame(150000, (int) TontinePayment::query()->sum('amount'));
        foreach (TontinePayment::query()->get() as $payment) {
            $this->assertSame($user->tenant_id, $payment->tenant_id);
            $this->assertSame(now()->toDateString(), $payment->payment_date->toDateString());
            $this->assertSame(['recorded'], array_column($payment->history, 'action'));
        }
    }

    public function test_duplicate_in_middle_of_batch_prevents_all_new_payments(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors();
        $this->paymentForm($user, $tontine)->set('paymentDate', '2026-10-14')->set('paymentEndDate', '2026-10-16')
            ->assertViewHas('batchPaidCount', 1)->call('save')->assertHasErrors('paymentDate');
        $this->assertDatabaseCount('tontine_days', 1);
        $this->assertDatabaseCount('tontine_payments', 1);
        $this->assertDatabaseMissing('tontine_days', ['due_date' => '2026-10-14']);
        $this->assertDatabaseMissing('tontine_days', ['due_date' => '2026-10-16']);
    }

    public function test_batch_preserves_existing_day_amount_and_cancelled_payment_history(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => '2026-10-15', 'amount' => 30000]);
        $this->paymentForm($user, $tontine)->set('amount', '30000')->call('save')->assertHasNoErrors();
        $payment = TontinePayment::query()->sole();
        Livewire::actingAs($user)->test(Cotisation::class)->call('confirmCancellation', $payment->id)
            ->set('cancellationReason', 'Erreur de saisie')->call('cancelPayment')->assertHasNoErrors();
        $tontine->update(['daily_amount' => 75000]);
        $this->paymentForm($user, $tontine)->set('paymentEndDate', '2026-10-17')->assertSet('amount', '180000')
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseCount('tontine_payments', 3);
        $this->assertSame(180000, (int) TontinePayment::query()->sum('amount'));
        $this->assertSame(30000, (int) $payment->fresh()->amount);
        $this->assertSame(['recorded', 'cancelled', 'recorded'], array_column($payment->fresh()->history, 'action'));
    }

    public function test_batch_rejects_reversed_future_outside_cycle_and_early_receipt_dates(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->set('paymentEndDate', '2026-10-14')->call('save')->assertHasErrors('paymentEndDate');
        $this->paymentForm($user, $tontine)->set('paymentEndDate', now()->addDay()->toDateString())->call('save')->assertHasErrors('paymentEndDate');
        $this->paymentForm($user, $tontine)->set('paymentDate', '2026-09-30')->set('paymentEndDate', '2026-10-02')->call('save')->assertHasErrors('paymentDate');
        $this->paymentForm($user, $tontine)->set('paymentEndDate', '2026-10-17')->set('receivedDate', '2026-10-16')->call('save')->assertHasErrors('receivedDate');
        $this->assertDatabaseCount('tontine_days', 0);
        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_batch_cannot_override_total_or_pay_another_tenant(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->set('paymentEndDate', '2026-10-17')->set('amount', '50000')
            ->call('save')->assertHasErrors('amount');
        [, $other] = $this->createTontine('beta');
        $this->paymentForm($user, $other)->set('paymentEndDate', '2026-10-17')->call('save')->assertHasErrors(['customerId', 'tontineId']);
        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_excessive_batch_is_rejected_without_creating_days(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $tontine->update(['start_date' => '2025-01-01']);
        $this->paymentForm($user, $tontine)->set('paymentDate', '2025-01-01')->set('paymentEndDate', '2026-10-15')
            ->call('save')->assertHasErrors('paymentEndDate');
        $this->assertDatabaseCount('tontine_days', 0);
    }

    public function test_selecting_tontine_opens_month_without_selecting_payments_automatically(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));
        Livewire::actingAs($user)->test(Cotisation::class)->set('customerId', (string) $tontine->customer_id)
            ->set('tontineId', (string) $tontine->id)->assertSet('selectedMonth', '2026-10')
            ->assertSet('selectedDates', [])->assertSet('amount', '0')
            ->assertSee('Cliquez sur une journée ou ajoutez une période');
        $this->assertDatabaseCount('tontine_days', 0);
    }

    public function test_month_selection_is_explicit_and_clamped_to_cycle_and_today(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));
        $tontine->update(['start_date' => '2026-09-15', 'end_date' => '2026-11-15']);
        $page = Livewire::actingAs($user)->test(Cotisation::class)->set('customerId', (string) $tontine->customer_id)
            ->set('tontineId', (string) $tontine->id)->set('selectedMonth', '2026-09')->assertSet('selectedDates', [])
            ->call('selectMonth')->assertSet('selectedDates', fn ($dates) => count($dates) === 14 && $dates[0] === '2026-09-15' && end($dates) === '2026-09-30')
            ->assertSet('amount', '700000');
        $page->set('selectedMonth', '2026-11')->call('selectMonth')->assertHasErrors('selectedDates')
            ->assertSet('selectedDates', fn ($dates) => count($dates) === 14);
        $page->set('selectedMonth', '2026-08')->call('selectMonth')->assertHasErrors('selectedDates');
        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_monthly_history_uses_due_month_instead_of_receipt_month_and_excludes_cancelled_total(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->travelTo(Carbon::parse('2026-10-26 10:00:00'));
        $tontine->update(['start_date' => '2026-09-01']);
        $this->paymentForm($user, $tontine)->set('paymentDate', '2026-09-30')->call('save')->assertHasNoErrors();
        $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors();
        $this->paymentForm($user, $tontine)->set('paymentDate', '2026-10-16')->call('save')->assertHasNoErrors();
        $payment = TontinePayment::query()->latest('id')->first();
        $payment->update(['voided_at' => now()]);
        $page = Livewire::actingAs($user)->test(Cotisation::class)->assertViewHas('paymentMonths', fn ($groups) => $groups->count() === 2)
            ->assertViewHas('displayedTotal', 100000);
        $page->set('filterMonth', '2026-09')->assertViewHas('payments', fn ($rows) => $rows->count() === 1)
            ->assertViewHas('displayedTotal', 50000)->assertSee('Septembre 2026');
        $page->set('filterMonth', '2026-10')->assertViewHas('payments', fn ($rows) => $rows->count() === 2)
            ->assertViewHas('displayedTotal', 50000)->assertSee('Octobre 2026');
    }

    public function test_nonconsecutive_days_can_be_paid_without_filling_the_gaps(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $page = $this->calendarForm($user, $tontine)->call('toggleDay', '2026-10-03')->call('toggleDay', '2026-10-07')
            ->call('toggleDay', '2026-10-12')->assertSet('amount', '150000')
            ->assertViewHas('selectedPeriods', fn ($periods) => count($periods) === 3);
        $page->call('save')->assertHasNoErrors()->assertSet('selectedDates', [])->assertDispatched('payment-updated');
        $this->assertSame(['2026-10-03', '2026-10-07', '2026-10-12'], TontineDay::query()->orderBy('due_date')->get()->map(fn ($day) => $day->due_date->toDateString())->all());
        $this->assertDatabaseCount('tontine_payments', 3);
        $this->assertDatabaseMissing('tontine_days', ['due_date' => '2026-10-04']);
    }

    public function test_multiple_periods_merge_overlap_and_skip_previously_paid_days(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->set('paymentDate', '2026-10-03')->call('save')->assertHasNoErrors();
        $page = $this->calendarForm($user, $tontine)->set('periodStart', '2026-10-01')->set('periodEnd', '2026-10-04')->call('addPeriod')
            ->set('periodStart', '2026-10-04')->set('periodEnd', '2026-10-05')->call('addPeriod')
            ->set('periodStart', '2026-10-09')->set('periodEnd', '2026-10-10')->call('addPeriod')
            ->assertSet('selectedDates', ['2026-10-01', '2026-10-02', '2026-10-05', '2026-10-09', '2026-10-10'])
            ->assertSet('amount', '250000');
        $page->call('removePeriod', '2026-10-09', '2026-10-10')->assertSet('amount', '150000')
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseCount('tontine_payments', 4);
        $this->assertDatabaseMissing('tontine_days', ['due_date' => '2026-10-06']);
    }

    public function test_selection_survives_month_navigation_and_can_be_cleared(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $tontine->update(['start_date' => '2026-09-01']);
        $this->calendarForm($user, $tontine)->call('toggleDay', '2026-10-02')->call('changeMonth', -1)
            ->assertSet('selectedMonth', '2026-09')->call('toggleDay', '2026-09-25')->assertSet('amount', '100000')
            ->call('changeMonth', 1)->assertSet('selectedDates', ['2026-09-25', '2026-10-02'])
            ->call('toggleDay', '2026-10-02')->assertSet('selectedDates', ['2026-09-25'])
            ->call('clearSelection')->assertSet('selectedDates', [])->assertSet('amount', '0');
        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_paid_future_outside_and_empty_selections_cannot_be_paid(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->paymentForm($user, $tontine)->call('save')->assertHasNoErrors();
        $page = $this->calendarForm($user, $tontine);
        $page->call('toggleDay', '2026-10-15')->assertHasErrors('selectedDates');
        $page->call('toggleDay', '2026-10-27')->assertHasErrors('selectedDates');
        $page->call('toggleDay', '2026-09-30')->assertHasErrors('selectedDates');
        $page->call('save')->assertHasErrors('selectedDates');
        $this->assertDatabaseCount('tontine_payments', 1);
    }

    public function test_forged_selected_dates_are_validated_on_save(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->calendarForm($user, $tontine)->set('selectedDates', ['not-a-date'])->call('save')->assertHasErrors('selectedDates.0');
        $this->calendarForm($user, $tontine)->set('selectedDates', ['2026-10-03', '2026-10-03'])->call('save')->assertHasErrors('selectedDates.0');
        $this->calendarForm($user, $tontine)->set('selectedDates', ['2026-09-30'])->call('save')->assertHasErrors('selectedDates');
        $this->calendarForm($user, $tontine)->set('selectedDates', ['2026-10-03'])->set('receivedDate', '2026-10-02')->call('save')->assertHasErrors('selectedDates.0');
        $this->assertDatabaseCount('tontine_days', 0);
    }

    public function test_newly_paid_selection_is_rejected_atomically_and_old_amount_is_preserved(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => '2026-10-03', 'amount' => 30000]);
        $page = $this->calendarForm($user, $tontine)->call('toggleDay', '2026-10-03')->call('toggleDay', '2026-10-07')->assertSet('amount', '80000');
        $this->paymentForm($user, $tontine)->set('paymentDate', '2026-10-07')->call('save')->assertHasNoErrors();
        $page->call('save')->assertHasErrors('selectedDates');
        $this->assertDatabaseCount('tontine_payments', 1);
        $this->assertDatabaseMissing('tontine_payments', ['amount' => 30000]);
    }

    public function test_days_on_different_months_are_saved_with_their_actual_due_dates(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $tontine->update(['start_date' => '2026-09-01']);
        $this->calendarForm($user, $tontine)->set('periodStart', '2026-09-28')->set('periodEnd', '2026-09-29')->call('addPeriod')
            ->call('toggleDay', '2026-10-05')->assertSet('amount', '150000')->call('save')->assertHasNoErrors();
        $this->assertSame(['2026-09-28', '2026-09-29', '2026-10-05'], TontineDay::query()->orderBy('due_date')->get()->map(fn ($day) => $day->due_date->toDateString())->all());
        $this->assertDatabaseCount('tontine_payments', 3);
    }

    public function test_sundays_cannot_be_selected_or_submitted_as_contribution_days(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->calendarForm($user, $tontine)->call('toggleDay', '2026-10-04')->assertHasErrors('selectedDates');
        $this->calendarForm($user, $tontine)->set('selectedDates', ['2026-10-04'])->set('amount', '50000')
            ->call('save')->assertHasErrors('selectedDates');
        $this->actingAs($user)->get(route('cotisations', ['tontine' => $tontine->id, 'day' => '2026-10-04']))->assertUnprocessable();
        $this->assertDatabaseCount('tontine_payments', 0);
        $this->assertDatabaseCount('tontine_days', 0);
    }

    public function test_payment_period_skips_sunday_and_charges_only_contribution_days(): void
    {
        [$user, $tontine] = $this->createTontine('alpha');
        $this->calendarForm($user, $tontine)->set('paymentDate', '2026-10-03')->set('paymentEndDate', '2026-10-05')
            ->assertSet('amount', '100000')->call('save')->assertHasNoErrors();
        $this->assertDatabaseCount('tontine_payments', 2);
        $this->assertDatabaseMissing('tontine_days', ['due_date' => '2026-10-04']);
    }

    private function calendarForm(User $user, Tontine $tontine): Testable
    {
        $this->travelTo(Carbon::parse('2026-10-26 10:00:00'));

        return Livewire::actingAs($user)->test(Cotisation::class)->set('customerId', (string) $tontine->customer_id)->set('tontineId', (string) $tontine->id);
    }

    private function paymentForm(User $user, Tontine $tontine): Testable
    {
        return Livewire::actingAs($user)->test(Cotisation::class)
            ->set('customerId', (string) $tontine->customer_id)->set('tontineId', (string) $tontine->id)
            ->set('paymentDate', '2026-10-15')->set('amount', '50000');
    }

    /** @return array{User, Tontine} */
    private function createTontine(string $slug): array
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);
        $client = Customer::query()->create(['tenant_id' => $tenant->id, 'reference' => 'CL-'.$slug, 'name' => 'Cliente '.$slug, 'phone' => '620000001', 'status' => 'Actif']);
        $tontine = Tontine::query()->create(['tenant_id' => $tenant->id, 'customer_id' => $client->id, 'reference' => 'TN-'.$slug, 'daily_amount' => 50000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31', 'status' => 'active']);

        return [$user, $tontine];
    }
}
