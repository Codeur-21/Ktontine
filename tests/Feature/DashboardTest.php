<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Tontine;
use App\Models\TontineDay;
use App\Models\TontinePayment;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_history_counts_only_finished_fully_paid_cycles_and_recalculates_cancellations(): void
    {
        [$user, $customer, $paid] = $this->cycle('alpha');
        $paid->update(['start_date' => '2026-10-01', 'end_date' => '2026-10-01']);
        $payment = $this->payment($paid, '2026-10-01');
        $unpaid = Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-UNPAID', 'daily_amount' => 10000, 'start_date' => '2026-10-02', 'end_date' => '2026-10-03']);
        $this->payment($unpaid, '2026-10-02');
        $this->payment($unpaid, '2026-10-03', true);
        $future = Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-FUTURE', 'daily_amount' => 10000, 'start_date' => '2026-11-01', 'end_date' => '2026-11-01']);
        $this->payment($future, '2026-11-01');
        [, , $foreign] = $this->cycle('beta');
        $foreign->update(['start_date' => '2026-10-01', 'end_date' => '2026-10-01']);
        $this->payment($foreign, '2026-10-01');

        $component = Livewire::actingAs($user)->test(Dashboard::class)
            ->assertViewHas('annualEarnedCommission', 10000)
            ->assertViewHas('annualExpectedCommission', 30000)
            ->assertViewHas('commissionHistory', fn ($months) => $months->count() === 12 && $months[10]['earned'] === 10000 && $months[10]['expected'] === 20000 && $months[11]['earned'] === 0)
            ->set('commissionMonth', '2026-10')->assertSet('commissionPeriod', '2026-10');
        $payment->update(['voided_at' => now()]);
        $component->call('$refresh')->assertViewHas('annualEarnedCommission', 0)
            ->set('commissionMonth', '2025-10')->assertViewHas('historyYear', 2025)
            ->assertViewHas('annualExpectedCommission', 0);
    }

    public function test_empty_dashboard_shows_zeroes_and_empty_states_without_writing(): void
    {
        [$user] = $this->account('alpha');
        Livewire::actingAs($user)->test(Dashboard::class)->assertViewHas('nberClient', 0)
            ->assertViewHas('nberTontine', 0)->assertViewHas('dailyTotal', 0)->assertViewHas('weeklyTotal', 0)
            ->assertViewHas('overdueCustomers', 0)->assertViewHas('upcomingEnds', 0)->assertViewHas('priorityCount', 0)
            ->assertViewHas('weekDays', fn ($days) => $days->count() === 7 && $days->sum('amount') === 0)
            ->assertSee('Aucune cotisation enregistrée pour le moment.')->assertSee('Aucun versement enregistré cette semaine.')
            ->assertDontSee('Données d’exemple')->assertDontSee('NaN')->assertDontSee('INF');
        $this->assertDatabaseCount('tontine_payments', 0);
        $this->assertDatabaseCount('tontine_days', 0);
    }

    public function test_metrics_chart_priorities_and_transactions_use_only_the_current_tenant(): void
    {
        [$user, $customer, $tontine] = $this->cycle('alpha');
        $this->payment($tontine, '2026-10-26');
        $this->payment($tontine, '2026-10-27', true);
        $this->payment($tontine, '2026-10-28');
        $this->payment($tontine, '2026-10-25');
        Customer::query()->create(['name' => 'Inactive', 'status' => 'Inactif']);
        Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-SECOND', 'daily_amount' => 1000, 'start_date' => '2026-10-26', 'end_date' => '2026-11-02']);
        Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-CLOSED', 'daily_amount' => 1000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-28', 'status' => 'closed']);
        [, , $foreign] = $this->cycle('beta');
        $this->payment($foreign, '2026-10-28');

        $page = Livewire::actingAs($user)->test(Dashboard::class)->assertViewHas('nberClient', 1)
            ->assertViewHas('nberTontine', 2)->assertViewHas('dailyTotal', 10000)->assertViewHas('weeklyTotal', 20000)
            ->assertViewHas('overdueCustomers', 1)->assertViewHas('upcomingEnds', 2)->assertViewHas('priorityCount', 2)
            ->assertViewHas('weekDays', fn ($days) => $days->pluck('amount')->all() === [10000, 0, 10000, 0, 0, 0, 0])
            ->assertViewHas('lastCotisations', fn ($payments) => $payments->count() === 4)
            ->assertSee('Cliente alpha')->assertSee('TN-alpha')->assertSee('Annulé')->assertDontSee('Cliente beta');
        $this->actingAs($user);
        $tontine->payments()->where('payment_date', '2026-10-28')->firstOrFail()->update(['voided_at' => now()]);
        $page->call('$refresh')->assertViewHas('dailyTotal', 0)->assertViewHas('weeklyTotal', 10000);
    }

    public function test_fully_paid_days_and_today_do_not_count_as_arrears(): void
    {
        [$user, , $tontine] = $this->cycle('alpha');
        $this->payment($tontine, '2026-10-26');
        $this->payment($tontine, '2026-10-27');
        Livewire::actingAs($user)->test(Dashboard::class)->assertViewHas('overdueCustomers', 0);
        $tontine->update(['end_date' => '2026-10-27']);
        Livewire::actingAs($user)->test(Dashboard::class)->assertViewHas('overdueCustomers', 0)->assertViewHas('upcomingEnds', 0);
    }

    public function test_latest_ten_payments_are_ordered_and_customer_names_are_escaped(): void
    {
        [$user, $customer, $tontine] = $this->cycle('alpha');
        $tontine->update(['start_date' => '2026-10-01']);
        $customer->update(['name' => '<script>alert(1)</script>']);
        $ids = [];
        for ($index = 1; $index <= 12; $index++) {
            $ids[] = $this->payment($tontine, '2026-10-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT))->id;
        }
        Livewire::actingAs($user)->test(Dashboard::class)
            ->assertViewHas('lastCotisations', fn ($payments) => $payments->pluck('id')->all() === array_reverse(array_slice($ids, -10)))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_mismatched_payment_day_is_excluded_from_totals_and_recent_payments(): void
    {
        [$user, $customer, $tontine] = $this->cycle('alpha');
        $payment = $this->payment($tontine, '2026-10-28');
        $other = Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-OTHER', 'daily_amount' => 10000, 'start_date' => '2026-10-26', 'end_date' => '2026-11-02']);
        $day = TontineDay::query()->create(['tontine_id' => $other->id, 'due_date' => '2026-10-28', 'amount' => 10000]);
        DB::table('tontine_payments')->where('id', $payment->id)->update(['tontine_day_id' => $day->id]);
        Livewire::actingAs($user)->test(Dashboard::class)->assertViewHas('dailyTotal', 0)->assertViewHas('weeklyTotal', 0)
            ->assertViewHas('lastCotisations', fn ($payments) => $payments->isEmpty());
    }

    public function test_guest_and_suspended_tenant_cannot_view_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirectToRoute('login');
        [$user] = $this->account('alpha');
        $user->tenant()->update(['is_active' => false]);
        Livewire::actingAs($user)->test(Dashboard::class)->assertForbidden();
    }

    public function test_month_filter_sums_one_daily_stake_per_cycle_and_groups_commission_by_customer(): void
    {
        [$user, $customer, $cycle] = $this->cycle('alpha');
        $cycle->update(['start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
        Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-OCT-SECOND', 'daily_amount' => 3000, 'start_date' => '2026-09-20', 'end_date' => '2026-10-01', 'status' => 'closed', 'closed_at' => '2026-11-01']);
        $second = Customer::query()->create(['name' => 'Deuxième cliente', 'status' => 'Inactif']);
        Tontine::query()->create(['customer_id' => $second->id, 'reference' => 'TN-OCT-OTHER', 'daily_amount' => 5000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
        Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-NOV', 'daily_amount' => 2000, 'start_date' => '2026-10-28', 'end_date' => '2026-11-30']);
        Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-SEP', 'daily_amount' => 7000, 'start_date' => '2026-09-01', 'end_date' => '2026-09-30']);
        $editor = Livewire::actingAs($user)->test(Dashboard::class)->assertSet('commissionMonth', '2026-10')
            ->assertViewHas('commissionTotal', 18000)->assertViewHas('commissionCustomerCount', 2)->assertViewHas('commissionCycleCount', 3)
            ->assertViewHas('commissionCustomers', fn ($rows) => $rows->total() === 2 && (int) $rows->first()->monthly_commission === 13000 && (int) $rows->first()->cycles_count === 2)
            ->assertSee('13 000 GNF')->assertSee('Deuxième cliente');
        $editor->set('commissionMonth', '2026-09')->assertHasNoErrors()->assertViewHas('commissionTotal', 7000)
            ->assertViewHas('commissionCustomerCount', 1)->assertDontSee('Deuxième cliente')
            ->set('commissionMonth', '2026-11')->assertViewHas('commissionTotal', 2000)
            ->call('currentCommissionMonth')->assertSet('commissionMonth', '2026-10')->assertViewHas('commissionTotal', 18000);
        $this->assertDatabaseCount('tontine_payments', 0);
        $this->assertDatabaseCount('tontines', 5);
    }

    public function test_commission_excludes_foreign_tenants_and_mismatched_customers(): void
    {
        [$user, $customer, $cycle] = $this->cycle('alpha');
        $cycle->update(['end_date' => '2026-10-31']);
        [, $foreignCustomer, $foreign] = $this->cycle('beta');
        $foreign->update(['end_date' => '2026-10-31']);
        $this->actingAs($user);
        $mismatched = Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-BAD', 'daily_amount' => 90000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
        DB::table('tontines')->where('id', $mismatched->id)->update(['customer_id' => $foreignCustomer->id]);
        Livewire::actingAs($user)->test(Dashboard::class)->assertViewHas('commissionTotal', 10000)
            ->assertViewHas('commissionCustomerCount', 1)->assertViewHas('commissionCycleCount', 1)->assertDontSee('Cliente beta');
    }

    public function test_commission_filter_keeps_last_valid_month_on_invalid_input_and_handles_empty_month(): void
    {
        [$user, , $cycle] = $this->cycle('alpha');
        $cycle->update(['end_date' => '2026-10-31']);
        $editor = Livewire::actingAs($user)->test(Dashboard::class)->set('commissionMonth', 'invalid')
            ->assertHasErrors(['commissionMonth'])->assertSee('Choisissez un mois valide.')
            ->assertSet('commissionPeriod', '2026-10')->assertViewHas('commissionTotal', 10000);
        $editor->set('commissionMonth', '')->assertHasErrors(['commissionMonth'])
            ->assertSee('Choisissez un mois pour consulter votre commission.')
            ->set('commissionMonth', '2026-12')->assertHasNoErrors()->assertViewHas('commissionTotal', 0)
            ->assertSee('Aucune tontine n’arrive à échéance ce mois-ci.');
    }

    public function test_commission_pagination_does_not_change_total_and_resets_when_month_changes(): void
    {
        [$user] = $this->account('alpha');
        for ($index = 1; $index <= 9; $index++) {
            $customer = Customer::query()->create(['name' => 'Client commission '.$index]);
            Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-COM-'.$index, 'daily_amount' => 1000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
        }
        Livewire::actingAs($user)->test(Dashboard::class)->assertViewHas('commissionTotal', 9000)
            ->assertViewHas('commissionCustomers', fn ($rows) => $rows->count() === 8 && $rows->total() === 9)
            ->call('setPage', 2, 'commissionPage')->assertViewHas('commissionTotal', 9000)
            ->assertViewHas('commissionCustomers', fn ($rows) => $rows->count() === 1 && $rows->currentPage() === 2)
            ->set('commissionMonth', '2026-11')->assertViewHas('commissionTotal', 0)
            ->assertViewHas('commissionCustomers', fn ($rows) => $rows->currentPage() === 1);
    }

    public function test_rolling_month_commission_is_counted_once_in_the_month_of_expiry(): void
    {
        [$user] = $this->account('alpha');
        $customer = Customer::query()->create(['name' => 'Cliente cycle glissant']);
        $this->travelTo(Carbon::parse('2026-10-07 10:00:00'));
        Livewire::actingAs($user)->test(\App\Livewire\Tontine::class)
            ->set('customerId', (string) $customer->id)->set('dailyAmount', '10000')
            ->set('startDate', '2026-10-07')->assertSet('endDate', '2026-11-10')
            ->call('save')->assertHasNoErrors();
        $cycle = Tontine::query()->sole();
        $dashboard = Livewire::actingAs($user)->test(Dashboard::class)
            ->assertViewHas('commissionTotal', 0)->assertViewHas('commissionCustomerCount', 0)
            ->set('commissionMonth', '2026-11')->assertViewHas('commissionTotal', 10000)
            ->assertViewHas('commissionCustomerCount', 1)->assertViewHas('commissionCycleCount', 1)
            ->assertViewHas('commissionCustomers', fn ($rows) => (int) $rows->first()->monthly_commission === 10000)
            ->assertSee('10 000 GNF');
        $this->payment($cycle, '2026-10-07');
        $cancelledPayment = $this->payment($cycle, '2026-10-08', true);
        $dashboard->call('$refresh')->assertViewHas('commissionTotal', 10000);
        $cancelledPayment->update(['voided_at' => null]);
        $dashboard->call('$refresh')->assertViewHas('commissionTotal', 10000)
            ->set('commissionMonth', '2026-12')->assertViewHas('commissionTotal', 0);
        $this->assertDatabaseCount('tontines', 1);
        $this->assertDatabaseCount('tontine_payments', 2);
    }

    public function test_expiry_month_commission_includes_its_boundaries_and_excludes_invalid_cycles(): void
    {
        [$user, $customer, $cycle] = $this->cycle('alpha');
        $cycle->update(['end_date' => '2026-11-01']);
        Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-LAST-DAY', 'daily_amount' => 5000, 'start_date' => '2026-10-30', 'end_date' => '2026-11-30', 'status' => 'closed']);
        Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-NEXT-MONTH', 'daily_amount' => 7000, 'start_date' => '2026-11-01', 'end_date' => '2026-12-01']);
        Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-INVALID-DATES', 'daily_amount' => 90000, 'start_date' => '2026-12-01', 'end_date' => '2026-11-15']);
        Livewire::actingAs($user)->test(Dashboard::class)->set('commissionMonth', '2026-11')
            ->assertViewHas('commissionTotal', 15000)->assertViewHas('commissionCustomerCount', 1)
            ->assertViewHas('commissionCycleCount', 2)
            ->assertViewHas('commissionCustomers', fn ($rows) => $rows->total() === 1 && (int) $rows->first()->monthly_commission === 15000 && (int) $rows->first()->cycles_count === 2);
    }

    public function test_portfolio_commission_includes_all_current_customers_across_expiry_months(): void
    {
        [$user, $customer, $cycle] = $this->cycle('alpha');
        $cycle->update(['start_date' => '2026-09-26', 'end_date' => '2026-10-26', 'daily_amount' => 50000]);
        $second = Customer::query()->create(['name' => 'Second client']);
        Tontine::query()->create(['customer_id' => $second->id, 'reference' => 'TN-NOV-20000', 'daily_amount' => 20000, 'start_date' => '2026-10-27', 'end_date' => '2026-11-26']);
        Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-OLD-CLOSED', 'daily_amount' => 90000, 'start_date' => '2026-08-01', 'end_date' => '2026-08-31', 'status' => 'closed']);
        [, , $foreign] = $this->cycle('beta');
        $foreign->update(['daily_amount' => 80000]);
        $dashboard = Livewire::actingAs($user)->test(Dashboard::class)
            ->assertViewHas('portfolioCommissionTotal', 70000)->assertViewHas('portfolioCommissionCustomerCount', 2)
            ->assertViewHas('commissionTotal', 50000)->assertSee('70 000')->assertSee('Total prévu · tontines en cours');
        $dashboard->set('commissionMonth', '2026-11')->assertViewHas('commissionTotal', 20000)
            ->assertViewHas('portfolioCommissionTotal', 70000);
    }

    /** @return array{User} */
    private function account(string $slug): array
    {
        $this->travelTo(Carbon::parse('2026-10-28 10:00:00'));
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);

        return [$user];
    }

    /** @return array{User, Customer, Tontine} */
    private function cycle(string $slug): array
    {
        [$user] = $this->account($slug);
        $customer = Customer::query()->create(['name' => 'Cliente '.$slug]);
        $tontine = Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-'.$slug, 'daily_amount' => 10000, 'start_date' => '2026-10-26', 'end_date' => '2026-11-02']);

        return [$user, $customer, $tontine];
    }

    private function payment(Tontine $tontine, string $date, bool $voided = false): TontinePayment
    {
        $day = TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => $date, 'amount' => 10000]);

        return TontinePayment::query()->create(['tontine_id' => $tontine->id, 'tontine_day_id' => $day->id, 'amount' => 10000, 'payment_date' => $date, 'recorded_by' => auth()->id(), 'voided_at' => $voided ? now() : null]);
    }
}
