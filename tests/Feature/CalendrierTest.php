<?php

namespace Tests\Feature;

use App\Livewire\Calendrier;
use App\Livewire\Cotisation;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Tontine;
use App\Models\TontinePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CalendrierTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_calendar_navigates_and_links_to_payment_form_without_writing(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 10:00:00'));
        [$user, $tontine] = $this->createTontineForTenant('Espace Alpha', 'TN-001');

        Livewire::actingAs($user)->test(Calendrier::class)
            ->assertSet('month', '2026-10')->call('nextMonth')->assertSet('month', '2026-11')
            ->call('previousMonth')->assertSet('month', '2026-10')
            ->assertSee(route('cotisations', ['tontine' => $tontine->id, 'day' => '2026-10-15']))
            ->assertDontSee(route('cotisations', ['tontine' => $tontine->id, 'day' => '2026-10-16']))
            ->assertDontSee('togglePayment');

        $this->assertDatabaseCount('tontine_days', 0);
        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_calendar_reflects_payment_and_confirmed_cancellation_from_cotisations(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 10:00:00'));
        [$user, $tontine] = $this->createTontineForTenant('Espace Alpha', 'TN-001');
        Livewire::actingAs($user)->test(Cotisation::class)
            ->set('customerId', (string) $tontine->customer_id)->set('tontineId', (string) $tontine->id)
            ->set('paymentDate', '2026-10-15')->call('save')->assertHasNoErrors();
        $payment = TontinePayment::query()->sole();

        Livewire::actingAs($user)->test(Calendrier::class)->assertSee('50 000 GNF encaissé');
        Livewire::actingAs($user)->test(Cotisation::class)->call('confirmCancellation', $payment->id)
            ->set('cancellationReason', 'Erreur de saisie')->call('cancelPayment')->assertHasNoErrors();
        Livewire::actingAs($user)->test(Calendrier::class)->assertSee('0 GNF encaissé');

        $this->assertNotNull($payment->fresh()->voided_at);
    }

    public function test_owner_cannot_update_another_tenants_ticket(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 10:00:00'));
        [$user] = $this->createTontineForTenant('Espace Alpha', 'TN-001');
        [, $otherTontine] = $this->createTontineForTenant('Espace Beta', 'TN-002');

        Livewire::actingAs($user)
            ->test(Calendrier::class)
            ->call('selectTontine', $otherTontine->id)
            ->assertNotFound();

        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_tickets_can_be_filtered_by_client_or_reference(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 10:00:00'));
        [$user, $firstTontine] = $this->createTontineForTenant('Espace Alpha', 'TN-001');

        $secondClient = Customer::query()->create([
            'tenant_id' => $user->tenant_id,
            'reference' => 'CL-SEARCH',
            'name' => 'Mariam Barry',
            'phone' => '622000099',
            'is_active' => true,
        ]);
        Tontine::query()->create([
            'tenant_id' => $user->tenant_id,
            'customer_id' => $secondClient->id,
            'reference' => 'TN-SEARCH',
            'daily_amount' => 25000,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'status' => 'active',
        ]);

        Livewire::actingAs($user)
            ->test(Calendrier::class)
            ->set('search', $firstTontine->customer->name)
            ->assertSee('TN-001')
            ->assertDontSee('TN-SEARCH')
            ->set('search', 'TN-SEARCH')
            ->assertSee('Mariam Barry')
            ->assertDontSee('TN-001');
    }

    public function test_inconsistent_client_membership_is_hidden_and_cannot_receive_payments(): void
    {
        [$owner, $tontine] = $this->createTontineForTenant('Espace Alpha', 'TN-ALPHA');
        [, $foreignTontine] = $this->createTontineForTenant('Espace Beta', 'TN-BETA');
        DB::table('tontines')->where('id', $tontine->id)->update(['customer_id' => $foreignTontine->customer_id]);

        Livewire::actingAs($owner)->test(Calendrier::class)->assertDontSee('TN-ALPHA')
            ->call('selectTontine', $tontine->id)->assertNotFound();
        Livewire::actingAs($owner)->test(\App\Livewire\Tontine::class)->assertDontSee('TN-ALPHA');
        Livewire::actingAs($owner)->test(Cotisation::class)
            ->set('customerId', (string) $foreignTontine->customer_id)->assertDontSee('TN-ALPHA');

        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_commission_always_matches_the_daily_amount(): void
    {
        [$user, $tontine] = $this->createTontineForTenant('Espace Alpha', 'TN-001');

        $this->assertSame(50000, $tontine->fresh()->commission_amount);

        $tontine->update([
            'daily_amount' => 75000,
            'commission_amount' => 1,
        ]);

        $this->assertSame(75000, $tontine->fresh()->commission_amount);
    }

    public function test_only_three_tickets_are_shown_by_default_and_search_finds_the_rest(): void
    {
        [$user, $first] = $this->createTontineForTenant('Espace Alpha', 'TN-001');
        foreach (['TN-002', 'TN-003', 'TN-004', 'TN-005'] as $reference) {
            Tontine::query()->create([
                'customer_id' => $first->customer_id, 'reference' => $reference,
                'daily_amount' => 50000, 'start_date' => '2026-10-01',
                'end_date' => '2026-11-30', 'status' => 'active',
            ]);
        }

        $component = Livewire::actingAs($user)->test(Calendrier::class);
        $defaultIds = $component->get('defaultTicketIds');
        $this->assertCount(3, array_unique($defaultIds));
        $hidden = Tontine::query()->whereNotIn('id', $defaultIds)->firstOrFail();

        $component
            ->assertViewHas('tontines', fn ($tickets) => $tickets->count() === 3 && $tickets->pluck('id')->diff($defaultIds)->isEmpty())
            ->assertDontSee($hidden->reference)
            ->call('nextMonth')
            ->assertSet('defaultTicketIds', $defaultIds)
            ->assertViewHas('tontines', fn ($tickets) => $tickets->pluck('id')->diff($defaultIds)->isEmpty())
            ->set('search', $hidden->reference)->assertSee($hidden->reference)
            ->set('search', 'TN-005')->assertSee('TN-005')
            ->assertViewHas('tontines', fn ($tickets) => $tickets->count() === 1)
            ->set('search', $first->customer->name)
            ->assertViewHas('tontines', fn ($tickets) => $tickets->count() === 5)
            ->set('search', '  ')
            ->assertViewHas('tontines', fn ($tickets) => $tickets->count() === 3);
    }

    public function test_sunday_is_never_due_and_has_no_expected_collection(): void
    {
        $this->travelTo(Carbon::parse('2026-10-18 10:00:00'));
        [$user, $tontine] = $this->createTontineForTenant('Espace Alpha', 'TN-001');

        Livewire::actingAs($user)->test(Calendrier::class)
            ->assertViewHas('todayDueCount', 0)->assertViewHas('todayDueAmount', 0)
            ->assertViewHas('calendarDays', fn ($days) => $days->firstWhere('date', '2026-10-18')['is_due'] === false)
            ->assertDontSee(route('cotisations', ['tontine' => $tontine->id, 'day' => '2026-10-18']));
    }

    /**
     * @return array{User, Tontine}
     */
    private function createTontineForTenant(string $tenantName, string $reference): array
    {
        $tenant = Tenant::query()->create([
            'name' => $tenantName,
            'slug' => str($tenantName)->slug()->toString(),
            'is_active' => true,
        ]);

        $user = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Mamadou Diallo',
            'phone' => '6200000'.$tenant->id,
            'email' => 'owner'.$tenant->id.'@example.com',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $client = Customer::query()->create([
            'tenant_id' => $tenant->id,
            'reference' => 'CL-'.$tenant->id,
            'name' => 'Aissatou Camara',
            'phone' => '6210000'.$tenant->id,
            'is_active' => true,
        ]);

        $tontine = Tontine::query()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $client->id,
            'reference' => $reference,
            'daily_amount' => 50000,
            'start_date' => '2026-10-01',
            'end_date' => '2026-11-30',
            'status' => 'active',
        ]);

        return [$user, $tontine];
    }
}
