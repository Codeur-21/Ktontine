<?php

namespace Tests\Feature;

use App\ActivityReport;
use App\Livewire\Rapport;
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

class RapportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_counts_actual_receipt_dates_cancellations_and_closed_cycle_commissions(): void
    {
        [$user, $tontine] = $this->dataset('alpha');
        $this->pay($tontine, '2026-10-20', '2026-10-24');
        $this->pay($tontine, '2026-10-21', '2026-10-24', true);
        $this->pay($tontine, '2026-10-22', '2026-10-23');
        Tontine::query()->create(['customer_id' => $tontine->customer_id, 'reference' => 'TN-CLOSED', 'daily_amount' => 30000, 'start_date' => '2026-10-10', 'end_date' => '2026-10-15', 'status' => 'closed', 'closed_at' => '2026-10-24 09:00:00']);

        $report = (new ActivityReport)->build('2026-10-24', '2026-10-24');
        $this->assertSame(['paid' => 50000, 'payments' => 1, 'cancelled' => 50000, 'cancelledPayments' => 1, 'closedCycles' => 1, 'plannedCommission' => 30000, 'clients' => 1, 'overdueDays' => 0, 'overdueAmount' => 0], $report['summary']);
        $this->assertSame(1, $report['daily']->count());
        $this->assertSame(50000, $report['clients']->first()['paid']);
        Livewire::actingAs($user)->test(Rapport::class)->set('startDate', '2026-10-24')->set('endDate', '2026-10-24')
            ->call('generate')->assertHasNoErrors()->assertSee('50 000 GNF')->assertSet('reportStart', '2026-10-24');
    }

    public function test_late_payment_after_report_period_covers_day_in_current_arrears_but_is_not_period_income(): void
    {
        [$user, $tontine] = $this->dataset('alpha');
        $this->pay($tontine, '2026-10-20', '2026-10-24');
        $report = (new ActivityReport)->build('2026-10-20', '2026-10-22');

        $this->assertSame(0, $report['summary']['paid']);
        $this->assertSame(2, $report['summary']['overdueDays']);
        $this->assertSame(100000, $report['summary']['overdueAmount']);
    }

    public function test_period_arrears_respect_historical_daily_amounts_and_exclude_today(): void
    {
        [$user, $tontine] = $this->dataset('alpha');
        TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => '2026-10-20', 'amount' => 30000]);
        $this->pay($tontine, '2026-10-21', '2026-10-24');
        $this->pay($tontine, '2026-10-22', '2026-10-24', true);
        $report = (new ActivityReport)->build('2026-10-20', '2026-10-24');

        $this->assertSame(3, $report['summary']['overdueDays']);
        $this->assertSame(130000, $report['summary']['overdueAmount']);
    }

    public function test_invalid_period_does_not_replace_displayed_report(): void
    {
        [$user] = $this->dataset('alpha');
        Livewire::actingAs($user)->test(Rapport::class)->assertSet('reportStart', '2026-10-01')
            ->set('startDate', '2026-10-23')->set('endDate', '2026-10-20')->call('generate')
            ->assertHasErrors(['endDate' => 'after_or_equal'])->assertSet('reportStart', '2026-10-01')
            ->set('endDate', '2026-10-25')->call('generate')->assertHasErrors(['endDate' => 'before_or_equal'])
            ->set('startDate', 'invalid')->call('generate')->assertHasErrors('startDate');
    }

    public function test_other_tenants_never_appear_on_screen_print_or_csv(): void
    {
        [$owner, $alpha] = $this->dataset('alpha');
        $this->pay($alpha, '2026-10-20', '2026-10-24');
        [, $beta] = $this->dataset('beta');
        $this->pay($beta, '2026-10-20', '2026-10-24');

        Livewire::actingAs($owner)->test(Rapport::class)->assertDontSee('Cliente beta')
            ->assertViewHas('report', fn ($report) => $report['summary']['paid'] === 50000);
        $this->actingAs($owner)->get($this->exportUrl('print'))->assertOk()->assertDontSee('Cliente beta');
        $response = $this->actingAs($owner)->get($this->exportUrl('csv'))->assertOk()->assertDownload('rapport-2026-10-01-2026-10-24.csv');
        $this->assertStringContainsString('Cliente alpha', $response->streamedContent());
        $this->assertStringNotContainsString('Cliente beta', $response->streamedContent());
    }

    public function test_csv_neutralizes_formulas_and_print_escapes_client_names(): void
    {
        [$user, $tontine] = $this->dataset('alpha');
        $tontine->customer->update(['name' => '=1+1']);
        $this->pay($tontine, '2026-10-20', '2026-10-24');
        $response = $this->actingAs($user)->get($this->exportUrl('csv'))->assertOk();
        $this->assertStringContainsString("'=1+1", $response->streamedContent());
        $tontine->customer->update(['name' => '<script>alert(1)</script>']);
        $this->actingAs($user)->get($this->exportUrl('print'))->assertOk()->assertSee('window.print()', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_export_validates_period_and_guests_or_suspended_tenants_cannot_export(): void
    {
        $this->get($this->exportUrl('csv'))->assertRedirectToRoute('login');
        [$user] = $this->dataset('alpha');
        $this->actingAs($user)->getJson(route('rapports.export', ['startDate' => '2026-10-24', 'endDate' => '2026-10-20']))
            ->assertUnprocessable()->assertJsonValidationErrors('endDate');
        $user->tenant()->update(['is_active' => false]);
        $this->actingAs($user)->get($this->exportUrl('csv'))->assertForbidden();
        Livewire::actingAs($user)->test(Rapport::class)->assertForbidden();
    }

    public function test_empty_period_has_zero_totals_and_printable_empty_state(): void
    {
        [$user] = $this->dataset('alpha');
        Livewire::actingAs($user)->test(Rapport::class)->set('startDate', '2026-09-01')->set('endDate', '2026-09-30')
            ->call('generate')->assertHasNoErrors()->assertSee('Aucune activité sur cette période.')
            ->assertViewHas('report', fn ($report) => $report['summary']['paid'] === 0 && $report['summary']['overdueDays'] === 0);
    }

    public function test_pagination_does_not_limit_exports_and_new_period_resets_page(): void
    {
        [$user, $tontine] = $this->dataset('alpha');
        for ($index = 1; $index <= 15; $index++) {
            $client = Customer::query()->create(['name' => 'Autre '.$index, 'reference' => 'OTHER-'.$index]);
            Tontine::query()->create(['customer_id' => $client->id, 'reference' => 'TN-OTHER-'.$index, 'daily_amount' => 50000, 'start_date' => '2026-10-20', 'end_date' => '2026-10-26']);
        }
        Livewire::actingAs($user)->test(Rapport::class)->assertViewHas('clients', fn ($rows) => $rows->total() === 16 && $rows->count() === 15)
            ->call('nextPage')->assertViewHas('clients', fn ($rows) => $rows->currentPage() === 2 && $rows->count() === 1)
            ->call('currentMonth')->assertViewHas('clients', fn ($rows) => $rows->currentPage() === 1);
        $response = $this->actingAs($user)->get($this->exportUrl('csv'));
        $this->assertSame(17, count(explode("\n", trim($response->streamedContent()))));
    }

    private function exportUrl(string $format): string
    {
        return route('rapports.export', ['startDate' => '2026-10-01', 'endDate' => '2026-10-24', 'format' => $format]);
    }

    /** @return array{User, Tontine} */
    private function dataset(string $slug): array
    {
        $this->travelTo(Carbon::parse('2026-10-24 10:00:00'));
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);
        $client = Customer::query()->create(['reference' => 'CL-'.$slug, 'name' => 'Cliente '.$slug, 'phone' => '620000001']);
        $tontine = Tontine::query()->create(['customer_id' => $client->id, 'reference' => 'TN-'.$slug, 'daily_amount' => 50000, 'start_date' => '2026-10-20', 'end_date' => '2026-10-26']);

        return [$user, $tontine];
    }

    private function pay(Tontine $tontine, string $date, string $received, bool $voided = false): void
    {
        $day = TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => $date, 'amount' => 50000]);
        TontinePayment::query()->create(['tontine_id' => $tontine->id, 'tontine_day_id' => $day->id, 'amount' => 50000, 'payment_date' => $received, 'recorded_by' => auth()->id(), 'voided_at' => $voided ? now() : null]);
    }
}
