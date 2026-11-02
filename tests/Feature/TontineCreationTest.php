<?php

namespace Tests\Feature;

use App\Livewire\Tontine as TontineComponent;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Tontine;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TontineCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_finished_filter_includes_elapsed_and_closed_cycles_but_not_ongoing_or_foreign_cycles(): void
    {
        $this->travelTo(Carbon::parse('2026-10-29 12:00:00'));
        [$user, $customer] = $this->createCustomer('alpha');
        [, $foreign] = $this->createCustomer('beta');
        $this->actingAs($user);
        foreach ([['TN-PAST', $customer, 'active', '2026-10-28'], ['TN-TODAY', $customer, 'active', '2026-10-29'], ['TN-CLOSED', $customer, 'closed', '2026-11-01']] as [$reference, $owner, $status, $end]) {
            Tontine::query()->create(['customer_id' => $owner->id, 'reference' => $reference, 'daily_amount' => 10000, 'start_date' => '2026-10-01', 'end_date' => $end, 'status' => $status]);
        }
        $this->actingAs(User::query()->where('tenant_id', $foreign->tenant_id)->firstOrFail());
        Tontine::query()->create(['customer_id' => $foreign->id, 'reference' => 'TN-FOREIGN', 'daily_amount' => 10000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-28', 'status' => 'active']);
        Livewire::actingAs($user)->test(TontineComponent::class)
            ->set('statusFilter', 'finished')->assertSee('TN-PAST')->assertSee('TN-CLOSED')
            ->assertDontSee('TN-TODAY')->assertDontSee('TN-FOREIGN')
            ->assertSee('Terminée')->set('statusFilter', 'ongoing')
            ->assertSee('TN-TODAY')->assertDontSee('TN-PAST')->assertDontSee('TN-CLOSED');
    }

    public function test_owner_creates_a_tontine_for_a_customer_with_a_flash_message(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01 10:00:00'));
        [$user, $customer] = $this->createCustomer('alpha');

        $component = Livewire::actingAs($user)->test(TontineComponent::class)
            ->set('customerId', (string) $customer->id)
            ->set('dailyAmount', '50000')
            ->set('startDate', '2026-10-01')
            ->set('endDate', '2026-10-31')
            ->call('save')->assertHasNoErrors()->assertSet('customerId', '')
            ->assertSee($customer->name);

        $tontine = Tontine::query()->sole();
        $this->assertMatchesRegularExpression('/^TN-[A-Z0-9]{7}$/', $tontine->reference);
        $this->assertDatabaseHas('tontines', ['tenant_id' => $user->tenant_id, 'commission_amount' => 50000, 'daily_amount' => 50000, 'status' => 'active']);
        $this->assertDatabaseHas('customers', ['id' => $tontine->customer_id, 'tenant_id' => $user->tenant_id, 'reference' => $customer->reference, 'name' => $customer->name]);
        $component->assertSee('La tontine '.$tontine->reference.' a été créée avec succès.')->assertSee('Opération réussie');
    }

    public function test_invalid_amount_and_reversed_dates_do_not_create_a_tontine(): void
    {
        [$user, $customer] = $this->createCustomer('alpha');

        Livewire::actingAs($user)->test(TontineComponent::class)
            ->set('customerId', (string) $customer->id)->set('dailyAmount', '0')
            ->set('startDate', '2026-10-31')->set('endDate', '2026-10-01')
            ->call('save')->assertHasErrors(['dailyAmount' => 'min', 'endDate' => 'after_or_equal'])
            ->assertSee('Le montant doit être supérieur à zéro.');

        $this->assertDatabaseCount('tontines', 0);
        $this->assertNull(session('success'));
    }

    public function test_another_tenants_customer_is_not_visible_or_accepted(): void
    {
        [$user] = $this->createCustomer('alpha');
        [, $otherCustomer] = $this->createCustomer('beta');

        Livewire::actingAs($user)->test(TontineComponent::class)
            ->assertDontSee($otherCustomer->name)
            ->set('customerId', (string) $otherCustomer->id)->set('dailyAmount', '50000')
            ->set('startDate', '2026-10-01')->set('endDate', '2026-10-31')
            ->call('save')->assertHasErrors(['customerId' => 'exists']);

        $this->assertDatabaseCount('tontines', 0);
    }

    public function test_required_fields_are_reported_without_creating_a_tontine(): void
    {
        [$user] = $this->createCustomer('alpha');

        Livewire::actingAs($user)->test(TontineComponent::class)->call('save')
            ->assertHasErrors(['customerId' => 'required', 'dailyAmount' => 'required', 'startDate' => 'required', 'endDate' => 'required']);

        $this->assertDatabaseCount('tontines', 0);
    }

    public function test_guest_cannot_access_creation_pages(): void
    {
        $this->get(route('tontines'))->assertRedirectToRoute('login');
        $this->get(route('cotisations'))->assertRedirectToRoute('login');
    }

    public function test_month_fills_dates_including_leap_year_and_saves_them(): void
    {
        $this->travelTo(Carbon::parse('2028-01-01 10:00:00'));
        [$user, $customer] = $this->createCustomer('alpha');
        Livewire::actingAs($user)->test(TontineComponent::class)->set('month', '2028-02')
            ->assertSet('startDate', '2028-02-01')->assertSet('endDate', '2028-03-06')
            ->set('customerId', (string) $customer->id)->set('dailyAmount', '50000')
            ->call('save')->assertHasNoErrors()->assertSet('month', '');
        $this->assertDatabaseHas('tontines', ['start_date' => '2028-02-01', 'end_date' => '2028-03-06']);
    }

    public function test_start_date_calculates_thirty_contribution_days(): void
    {
        [$user] = $this->createCustomer('alpha');
        Livewire::actingAs($user)->test(TontineComponent::class)->set('startDate', '2026-11-12')
            ->assertSet('month', '2026-11')->assertSet('endDate', '2026-12-16')
            ->set('endDate', '2026-11-20')->assertSet('endDate', '2026-11-20');
    }

    #[DataProvider('rollingMonths')]
    public function test_thirty_contribution_days_handle_month_ends_and_persist_calculated_end(string $start, string $end): void
    {
        $this->travelTo(Carbon::parse($start.' 10:00:00'));
        [$user, $customer] = $this->createCustomer('alpha');
        Livewire::actingAs($user)->test(TontineComponent::class)->set('startDate', $start)
            ->assertSet('endDate', $end)->set('customerId', (string) $customer->id)->set('dailyAmount', '5000')
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('tontines', ['customer_id' => $customer->id, 'start_date' => $start, 'end_date' => $end]);
    }

    public static function rollingMonths(): array
    {
        return [
            ['2026-10-07', '2026-11-10'],
            ['2027-01-31', '2027-03-06'],
            ['2028-01-31', '2028-03-04'],
            ['2026-03-31', '2026-05-04'],
            ['2026-12-31', '2027-02-03'],
            ['2026-11-01', '2026-12-05'],
        ];
    }

    public function test_clearing_or_invalidating_start_date_clears_automatic_end(): void
    {
        [$user] = $this->createCustomer('alpha');
        Livewire::actingAs($user)->test(TontineComponent::class)->set('startDate', '2026-10-07')
            ->assertSet('endDate', '2026-11-10')->set('startDate', '')->assertSet('endDate', '')
            ->set('startDate', '2026-10-07')->set('startDate', '2026-02-31')->assertSet('endDate', '')
            ->call('save')->assertHasErrors(['startDate', 'endDate']);
        $this->assertDatabaseCount('tontines', 0);
    }

    public function test_tontine_cannot_start_in_the_past_even_when_the_date_is_submitted_manually(): void
    {
        $this->travelTo(Carbon::parse('2026-11-02 10:00:00'));
        [$user, $customer] = $this->createCustomer('alpha');

        Livewire::actingAs($user)->test(TontineComponent::class)
            ->set('customerId', (string) $customer->id)->set('dailyAmount', '50000')
            ->set('startDate', '2026-11-01')->call('save')
            ->assertHasErrors(['startDate' => 'after_or_equal'])
            ->assertSee('La tontine ne peut pas commencer avant aujourd’hui.');
        $this->assertDatabaseCount('tontines', 0);
        $this->assertDatabaseCount('tontine_days', 0);
    }

    public function test_selecting_the_current_month_starts_today_and_keeps_thirty_contribution_days(): void
    {
        $this->travelTo(Carbon::parse('2026-11-02 10:00:00'));
        [$user, $customer] = $this->createCustomer('alpha');

        Livewire::actingAs($user)->test(TontineComponent::class)
            ->set('month', '2026-11')->assertSet('startDate', '2026-11-02')->assertSet('endDate', '2026-12-05')
            ->set('customerId', (string) $customer->id)->set('dailyAmount', '50000')
            ->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('tontines', ['start_date' => '2026-11-02', 'end_date' => '2026-12-05']);
    }

    public function test_selecting_a_past_month_cannot_create_a_tontine(): void
    {
        $this->travelTo(Carbon::parse('2026-11-02 10:00:00'));
        [$user, $customer] = $this->createCustomer('alpha');

        Livewire::actingAs($user)->test(TontineComponent::class)
            ->set('month', '2026-10')->set('customerId', (string) $customer->id)->set('dailyAmount', '50000')
            ->call('save')->assertHasErrors(['startDate' => 'after_or_equal']);
        $this->assertDatabaseCount('tontines', 0);
    }

    /** @return array{User, Customer} */
    private function createCustomer(string $slug): array
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);
        $customer = Customer::query()->create(['name' => 'Cliente '.$slug, 'phone' => '620000001', 'address' => 'Conakry', 'status' => 'Actif']);

        return [$user, $customer];
    }
}
