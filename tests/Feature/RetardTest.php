<?php

namespace Tests\Feature;

use App\Livewire\Cotisation;
use App\Livewire\Retard;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Tontine;
use App\Models\TontineDay;
use App\Models\TontinePayment;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class RetardTest extends TestCase
{
    use RefreshDatabase;

    public function test_sunday_does_not_create_arrears_and_first_due_date_skips_it(): void
    {
        [$user, $tontine] = $this->tontine('alpha');
        $tontine->update(['start_date' => '2026-10-18']);
        $this->travelTo(Carbon::parse('2026-10-19 10:00:00'));

        Livewire::actingAs($user)->test(Retard::class)
            ->assertViewHas('totals', ['clients' => 0, 'tontines' => 0, 'days' => 0, 'amount' => 0]);

        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));
        Livewire::actingAs($user)->test(Retard::class)
            ->assertViewHas('totals', ['clients' => 1, 'tontines' => 1, 'days' => 1, 'amount' => 50000])
            ->assertViewHas('rows', fn ($rows) => $rows->first()['firstDue']->toDateString() === '2026-10-19');
    }

    public function test_missing_days_are_detected_even_when_no_day_rows_have_been_created(): void
    {
        [$user, $tontine] = $this->tontine('alpha');

        Livewire::actingAs($user)->test(Retard::class)
            ->assertViewHas('totals', ['clients' => 1, 'tontines' => 1, 'days' => 4, 'amount' => 200000])
            ->assertSee(route('cotisations', ['tontine' => $tontine->id, 'day' => '2026-10-20']))
            ->assertSee('200 000 GNF');
        $this->assertDatabaseCount('tontine_days', 0);
        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_only_active_payments_cover_due_days_and_historical_amounts_are_preserved(): void
    {
        [$user, $tontine] = $this->tontine('alpha');
        TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => '2026-10-20', 'amount' => 30000]);
        $this->pay($tontine, '2026-10-21');
        $this->pay($tontine, '2026-10-22', true);
        $this->pay($tontine, '2026-10-24');

        Livewire::actingAs($user)->test(Retard::class)
            ->assertViewHas('totals', ['clients' => 1, 'tontines' => 1, 'days' => 3, 'amount' => 130000])
            ->assertViewHas('rows', fn ($rows) => $rows->first()['firstDue']->toDateString() === '2026-10-20');
    }

    public function test_late_regulation_removes_the_due_day_and_cancellation_restores_it(): void
    {
        [$user, $tontine] = $this->tontine('alpha');
        Livewire::actingAs($user)->test(Cotisation::class)->set('customerId', (string) $tontine->customer_id)
            ->set('tontineId', (string) $tontine->id)->set('paymentDate', '2026-10-20')
            ->set('receivedDate', '2026-10-24')->call('save')->assertHasNoErrors();
        Livewire::actingAs($user)->test(Retard::class)
            ->assertViewHas('totals', fn ($totals) => $totals['days'] === 3 && $totals['amount'] === 150000)
            ->assertViewHas('rows', fn ($rows) => $rows->first()['firstDue']->toDateString() === '2026-10-21');
        $payment = TontinePayment::query()->sole();
        Livewire::actingAs($user)->test(Cotisation::class)->call('confirmCancellation', $payment->id)
            ->set('cancellationReason', 'Erreur de saisie')->call('cancelPayment')->assertHasNoErrors();
        Livewire::actingAs($user)->test(Retard::class)
            ->assertViewHas('totals', fn ($totals) => $totals['days'] === 4 && $totals['amount'] === 200000);
    }

    public function test_today_future_and_closed_cycles_are_excluded_and_past_cycles_stop_at_end_date(): void
    {
        [$user, $past] = $this->tontine('alpha');
        $past->update(['end_date' => '2026-10-21']);
        foreach ([['TN-TODAY', '2026-10-24', 'active'], ['TN-FUTURE', '2026-10-25', 'active'], ['TN-CLOSED', '2026-10-20', 'closed']] as [$reference, $start, $status]) {
            Tontine::query()->create(['customer_id' => $past->customer_id, 'reference' => $reference, 'daily_amount' => 50000, 'start_date' => $start, 'end_date' => '2026-10-30', 'status' => $status]);
        }
        Livewire::actingAs($user)->test(Retard::class)
            ->assertViewHas('totals', ['clients' => 1, 'tontines' => 1, 'days' => 2, 'amount' => 100000])
            ->assertDontSee('TN-TODAY')->assertDontSee('TN-FUTURE')->assertDontSee('TN-CLOSED');
    }

    public function test_fully_paid_tontine_disappears_from_arrears(): void
    {
        [$user, $tontine] = $this->tontine('alpha');
        foreach (['20', '21', '22', '23'] as $day) {
            $this->pay($tontine, '2026-10-'.$day);
        }
        Livewire::actingAs($user)->test(Retard::class)
            ->assertViewHas('totals', ['clients' => 0, 'tontines' => 0, 'days' => 0, 'amount' => 0])
            ->assertSee('Aucune cotisation en retard.');
    }

    public function test_other_tenants_are_hidden_from_totals_and_search(): void
    {
        [$owner] = $this->tontine('alpha');
        $this->tontine('beta');
        Livewire::actingAs($owner)->test(Retard::class)->assertDontSee('TN-beta')
            ->assertViewHas('totals', fn ($totals) => $totals['tontines'] === 1)
            ->set('search', 'beta')->assertViewHas('rows', fn ($rows) => $rows->total() === 0)
            ->assertDontSee('TN-beta');
    }

    public function test_filters_find_client_phone_reference_and_reset_pagination(): void
    {
        [$user, $tontine] = $this->tontine('alpha');
        $page = Livewire::actingAs($user)->test(Retard::class)->set('search', 'Cliente alpha')
            ->assertSee('TN-alpha')->set('search', '620000001')->assertSee('TN-alpha')
            ->set('search', 'TN-alpha')->assertSee('TN-alpha')
            ->set('minimumDays', '7')->assertViewHas('rows', fn ($rows) => $rows->total() === 0)
            ->call('clearFilters')->assertSet('search', '')->assertSet('minimumDays', '1');
        for ($index = 1; $index <= 15; $index++) {
            Tontine::query()->create(['customer_id' => $tontine->customer_id, 'reference' => 'TN-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT), 'daily_amount' => 50000, 'start_date' => '2026-10-20', 'end_date' => '2026-10-26']);
        }
        $page->call('$refresh')->assertViewHas('rows', fn ($rows) => $rows->total() === 16 && $rows->count() === 15)
            ->call('nextPage')->assertViewHas('rows', fn ($rows) => $rows->currentPage() === 2 && $rows->count() === 1)
            ->set('search', 'TN-alpha')->assertViewHas('rows', fn ($rows) => $rows->currentPage() === 1 && $rows->total() === 1);
    }

    public function test_guest_and_suspended_tenant_cannot_view_arrears(): void
    {
        $this->get(route('retards'))->assertRedirectToRoute('login');
        [$user] = $this->tontine('alpha');
        $user->tenant()->update(['is_active' => false]);
        $this->actingAs($user)->get(route('retards'))->assertForbidden();
        Livewire::actingAs($user)->test(Retard::class)->assertForbidden();
    }

    /** @return array{User, Tontine} */
    private function tontine(string $slug): array
    {
        $this->travelTo(Carbon::parse('2026-10-24 10:00:00'));
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);
        $client = Customer::query()->create(['reference' => 'CL-'.$slug, 'name' => 'Cliente '.$slug, 'phone' => '620000001']);
        $tontine = Tontine::query()->create(['customer_id' => $client->id, 'reference' => 'TN-'.$slug, 'daily_amount' => 50000, 'start_date' => '2026-10-20', 'end_date' => '2026-10-26', 'status' => 'active']);

        return [$user, $tontine];
    }

    private function pay(Tontine $tontine, string $date, bool $voided = false): void
    {
        $day = TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => $date, 'amount' => 50000]);
        TontinePayment::query()->create(['tontine_id' => $tontine->id, 'tontine_day_id' => $day->id, 'amount' => 50000, 'payment_date' => '2026-10-24', 'recorded_by' => auth()->id(), 'voided_at' => $voided ? now() : null]);
    }
}
