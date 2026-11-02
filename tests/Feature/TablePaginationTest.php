<?php

namespace Tests\Feature;

use App\Livewire\Client;
use App\Livewire\Cotisation;
use App\Livewire\Dashboard;
use App\Livewire\Equipe;
use App\Livewire\Tontine as TontinePage;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Tontine;
use App\Models\TontineDay;
use App\Models\TontinePayment;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TablePaginationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('tables')]
    public function test_business_tables_limit_rows_and_allow_access_to_next_page(string $component, string $variable, string $pageName): void
    {
        $user = $this->businessRecords();
        $page = Livewire::actingAs($user)->test($component)
            ->assertViewHas($variable, fn ($rows) => $rows->total() === 12 && $rows->count() === 10)
            ->assertSee('Aller à la page 2');
        $firstIds = $page->viewData($variable)->pluck('id')->all();
        $page->call('setPage', 2, $pageName)
            ->assertViewHas($variable, fn ($rows) => $rows->total() === 12 && $rows->count() === 2 && $rows->currentPage() === 2);
        $this->assertSame([], array_values(array_intersect($firstIds, $page->viewData($variable)->pluck('id')->all())));
        $page->assertDontSee('Client étranger');
    }

    public static function tables(): array
    {
        return [
            [Client::class, 'clients', 'page'],
            [TontinePage::class, 'tontines', 'page'],
            [Cotisation::class, 'payments', 'page'],
            [Dashboard::class, 'lastCotisations', 'paymentsPage'],
        ];
    }

    public function test_search_and_payment_filters_reset_pagination(): void
    {
        $user = $this->businessRecords();
        Livewire::actingAs($user)->test(Client::class)->call('setPage', 2)
            ->set('search', 'Client 01')->assertViewHas('clients', fn ($rows) => $rows->total() === 1 && $rows->currentPage() === 1);
        $payments = Livewire::actingAs($user)->test(Cotisation::class)->call('setPage', 2)
            ->set('search', 'Client 01')->assertViewHas('payments', fn ($rows) => $rows->total() === 1 && $rows->currentPage() === 1);
        $payments->set('search', '')->call('setPage', 2)->set('filterMonth', '2026-09')
            ->assertViewHas('payments', fn ($rows) => $rows->total() === 0 && $rows->currentPage() === 1);
        $payments->set('filterMonth', '')->call('setPage', 2)->set('filterDate', '2026-10-19')
            ->assertViewHas('payments', fn ($rows) => $rows->total() === 0 && $rows->currentPage() === 1);
    }

    public function test_dashboard_payment_and_commission_pages_are_independent(): void
    {
        $user = $this->businessRecords();
        Livewire::actingAs($user)->test(Dashboard::class)->call('setPage', 2, 'paymentsPage')
            ->assertViewHas('lastCotisations', fn ($rows) => $rows->currentPage() === 2)
            ->assertViewHas('commissionCustomers', fn ($rows) => $rows->currentPage() === 1)
            ->call('setPage', 2, 'commissionPage')
            ->assertViewHas('lastCotisations', fn ($rows) => $rows->currentPage() === 2)
            ->assertViewHas('commissionCustomers', fn ($rows) => $rows->currentPage() === 2)
            ->set('commissionMonth', '2026-09')
            ->assertViewHas('lastCotisations', fn ($rows) => $rows->currentPage() === 2)
            ->assertViewHas('commissionCustomers', fn ($rows) => $rows->currentPage() === 1);
    }

    public function test_team_is_paginated_and_search_returns_to_first_page(): void
    {
        $user = $this->businessRecords();
        for ($index = 1; $index <= 11; $index++) {
            UserFactory::new()->create(['tenant_id' => $user->tenant_id, 'role' => 'agent', 'name' => 'Agent '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
        }
        Livewire::actingAs($user)->test(Equipe::class)
            ->assertViewHas('members', fn ($rows) => $rows->total() === 12 && $rows->count() === 10)
            ->call('setPage', 2)->assertViewHas('members', fn ($rows) => $rows->count() === 2)
            ->set('search', 'Agent 01')->assertViewHas('members', fn ($rows) => $rows->total() === 1 && $rows->currentPage() === 1);
    }

    private function businessRecords(): User
    {
        $this->travelTo(now()->setDate(2026, 10, 28)->startOfDay());
        $foreignTenant = Tenant::query()->create(['name' => 'Étranger', 'slug' => 'foreign', 'is_active' => true]);
        $foreignUser = UserFactory::new()->create(['tenant_id' => $foreignTenant->id, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($foreignUser);
        $foreignCustomer = Customer::query()->create(['name' => 'Client étranger']);
        Tontine::query()->create(['customer_id' => $foreignCustomer->id, 'reference' => 'TN-FOREIGN', 'daily_amount' => 90000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
        $tenant = Tenant::query()->create(['name' => 'Mon espace', 'slug' => 'local', 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);
        for ($index = 1; $index <= 12; $index++) {
            $customer = Customer::query()->create(['name' => 'Client '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
            $cycle = Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-'.$index, 'daily_amount' => 1000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
            $day = TontineDay::query()->create(['tontine_id' => $cycle->id, 'due_date' => '2026-10-20', 'amount' => 1000]);
            TontinePayment::query()->create(['tontine_id' => $cycle->id, 'tontine_day_id' => $day->id, 'amount' => 1000, 'payment_date' => '2026-10-20', 'recorded_by' => $user->id]);
        }

        return $user;
    }
}
