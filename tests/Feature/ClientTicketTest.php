<?php

namespace Tests\Feature;

use App\Livewire\Client as ClientComponent;
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

class ClientTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_ticket_displays_identity_totals_and_print_action_without_writing(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => '2026-10-20', 'amount' => 30000]);
        $this->pay($tontine, '2026-10-21');
        $this->pay($tontine, '2026-10-22', true);

        $response = $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]));
        $response->assertOk()->assertSee('Cliente alpha')->assertSee('Adresse alpha')->assertSee('Espace alpha')
            ->assertSee('TN-alpha')->assertSee('Annulé')->assertSee('window.print()', false)->assertSee('@media print', false)
            ->assertViewHas('totals', ['expected' => 280000, 'paid' => 50000, 'remaining' => 230000, 'overdue' => 130000])
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertDatabaseCount('tontine_payments', 2);
        $this->assertDatabaseCount('tontine_days', 3);
    }

    public function test_ticket_shows_registration_month_and_calendar_states_without_creating_days(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        $customer->update(['name' => 'Mamadou Alpha Diallo']);
        $this->pay($tontine, '2026-10-21');
        $this->pay($tontine, '2026-10-22', true);
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))
            ->assertOk()->assertSee('Nom et prénom(s)')->assertSee('Mamadou Alpha Diallo')
            ->assertSee('Date d’enregistrement')->assertSee('24/10/2026')->assertSee('octobre 2026')
            ->assertSee('Calendrier des cotisations')->assertSee('Votre suivi avec Ktontine')
            ->assertViewHas('cycles', function ($cycles): bool {
                $days = $cycles->first()['calendar']->flatten(1)->keyBy('date');

                return $days['2026-10-19']['state'] === 'outside'
                    && $days['2026-10-20']['state'] === 'overdue'
                    && $days['2026-10-21']['state'] === 'paid'
                    && $days['2026-10-22']['state'] === 'cancelled'
                    && $days['2026-10-24']['state'] === 'due'
                    && $days['2026-10-24']['today'] === true
                    && $days['2026-10-27']['state'] === 'outside';
            });
        $this->assertDatabaseCount('tontine_days', 2);
        $this->assertDatabaseCount('tontine_payments', 2);
    }

    public function test_closed_cycle_calendar_does_not_mark_unpaid_days_as_overdue(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        $tontine->update(['status' => 'closed']);
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))->assertOk()
            ->assertViewHas('cycles', fn ($cycles) => $cycles->first()['calendar']->flatten(1)->keyBy('date')['2026-10-20']['state'] === 'unpaid');
    }

    public function test_card_totals_only_include_the_displayed_month_and_grid_has_ten_columns(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        $tontine->update(['start_date' => '2026-09-30']);
        $this->pay($tontine, '2026-09-30');
        $this->pay($tontine, '2026-10-21');
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))->assertOk()
            ->assertSee('Carte tontine')->assertSee('Montant total versé')->assertSee('Reste à cotiser')
            ->assertViewHas('cycles', function ($cycles): bool {
                $cycle = $cycles->first();

                return $cycle['monthlyExpected'] === 1100000 && $cycle['monthlyPaid'] === 50000
                    && $cycle['monthlyRemaining'] === 1050000 && $cycle['paid'] === 100000
                    && $cycle['cardCalendar']->count() === 4 && $cycle['cardCalendar']->every(fn ($row) => $row->count() === 10);
            });
    }

    public function test_card_marks_nonexistent_february_days_unavailable(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        $tontine->update(['start_date' => '2027-02-01', 'end_date' => '2027-02-28']);
        $this->travelTo(Carbon::parse('2027-02-10 10:00:00'));
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))->assertOk()
            ->assertViewHas('cycles', function ($cycles): bool {
                $days = $cycles->first()['cardCalendar']->flatten(1)->keyBy('day');

                return $days[28]['state'] === 'outside' && $days[29]['state'] === 'unavailable'
                    && $days[30]['state'] === 'unavailable' && $days[31]['state'] === 'unavailable'
                    && $cycles->first()['monthlyExpected'] === 1200000;
            });
    }

    public function test_customer_without_tontine_has_a_printable_empty_ticket(): void
    {
        [$user, $customer] = $this->dataset('alpha');
        $otherCustomer = Customer::query()->create(['name' => 'Sans tontine', 'phone' => '620000002', 'address' => 'Conakry', 'status' => 'Actif']);
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $otherCustomer->id]))
            ->assertOk()->assertSee('Aucune tontine rattachée')
            ->assertViewHas('totals', ['expected' => 0, 'paid' => 0, 'remaining' => 0, 'overdue' => 0]);
    }

    public function test_ticket_uses_the_direct_customer_relationship(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        $this->assertSame($customer->id, $tontine->customer_id);
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))
            ->assertOk()->assertSee('TN-alpha')->assertViewHas('cycles', fn ($cycles) => $cycles->count() === 1);
    }

    public function test_ticket_cannot_be_read_by_another_tenant(): void
    {
        [$owner] = $this->dataset('alpha');
        [, $foreignCustomer] = $this->dataset('beta');
        $this->actingAs($owner)->get(route('clients.ticket', ['customer' => $foreignCustomer->id]))->assertNotFound();
    }

    public function test_same_tenant_other_customer_and_foreign_payment_are_not_included(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        $otherClient = Customer::query()->create(['reference' => 'SECRET', 'name' => 'Client secret']);
        $otherTontine = Tontine::query()->create(['customer_id' => $otherClient->id, 'reference' => 'TN-SECRET', 'daily_amount' => 50000, 'start_date' => '2026-10-20', 'end_date' => '2026-10-26']);
        $this->pay($tontine, '2026-10-21');
        $payment = TontinePayment::query()->sole();
        DB::table('tontine_payments')->where('id', $payment->id)->update(['tontine_id' => $otherTontine->id]);
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))
            ->assertOk()->assertDontSee('TN-SECRET')->assertDontSee('Client secret')
            ->assertViewHas('totals', fn ($totals) => $totals['paid'] === 0);
    }

    public function test_closed_cycles_are_listed_without_adding_to_active_arrears(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        $tontine->update(['status' => 'closed', 'closed_at' => now()]);
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))
            ->assertOk()->assertSee('Clôturée')
            ->assertViewHas('totals', ['expected' => 300000, 'paid' => 0, 'remaining' => 300000, 'overdue' => 0]);
    }

    public function test_guest_suspended_tenant_and_deleted_customer_cannot_print(): void
    {
        $this->get(route('clients.ticket', ['customer' => 1]))->assertRedirectToRoute('login');
        [$user, $customer] = $this->dataset('alpha');
        DB::table('customers')->where('id', $customer->id)->update(['deleted_at' => now()]);
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))->assertNotFound();
        $user->tenant()->update(['is_active' => false]);
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))->assertForbidden();
    }

    public function test_ticket_escapes_client_content_and_is_available_from_client_list(): void
    {
        [$user, $customer] = $this->dataset('alpha');
        $customer->update(['name' => '<script>alert(1)</script>']);
        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))
            ->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        Livewire::actingAs($user)->test(ClientComponent::class)
            ->assertSee(route('clients.ticket', ['customer' => $customer->id]))->assertSee('Ticket / Imprimer');
    }

    public function test_ticket_defaults_to_the_latest_contribution_month_after_the_month_changes(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        $this->pay($tontine, '2026-10-21');
        $this->travelTo(Carbon::parse('2026-11-02 10:00:00'));

        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))
            ->assertOk()->assertViewHas('selectedMonth', '2026-10')
            ->assertSee('octobre 2026')
            ->assertViewHas('cycles', fn ($cycles) => $cycles->first()['monthlyPaid'] === 50000
                && $cycles->first()['cardCalendar']->flatten(1)->keyBy('day')[21]['state'] === 'paid');
    }

    public function test_ticket_can_display_a_previous_month_while_preserving_full_cycle_totals(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        $tontine->update(['start_date' => '2026-09-30']);
        $this->pay($tontine, '2026-09-30');
        $this->pay($tontine, '2026-10-21');

        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id, 'month' => '2026-09']))
            ->assertOk()->assertViewHas('selectedMonth', '2026-09')
            ->assertSee('septembre 2026')->assertSee('Total du cycle versé')->assertSee('Mois affiché')
            ->assertViewHas('cycles', fn ($cycles) => $cycles->first()['monthlyPaid'] === 50000
                && $cycles->first()['paid'] === 100000
                && $cycles->first()['cardCalendar']->flatten(1)->keyBy('day')[30]['state'] === 'paid');
        $this->assertDatabaseCount('tontine_payments', 2);
    }

    public function test_ticket_preserves_historical_sunday_payments_without_charging_a_sunday(): void
    {
        [$user, $customer, $tontine] = $this->dataset('alpha');
        $this->pay($tontine, '2026-10-25');

        $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id]))
            ->assertOk()->assertSee('25/10/2026')
            ->assertViewHas('cycles', fn ($cycles) => $cycles->first()['paid'] === 50000
                && $cycles->first()['expected'] === 300000
                && $cycles->first()['monthlyExpected'] === 300000
                && $cycles->first()['payments']->count() === 1);
        $this->assertDatabaseCount('tontine_payments', 1);
    }

    public function test_ticket_rejects_invalid_month_filters(): void
    {
        [$user, $customer] = $this->dataset('alpha');

        foreach (['2026-13', 'not-a-month', ['2026-10']] as $month) {
            $this->actingAs($user)->get(route('clients.ticket', ['customer' => $customer->id, 'month' => $month]))
                ->assertUnprocessable();
        }
    }

    /** @return array{User, Customer, Tontine} */
    private function dataset(string $slug): array
    {
        $this->travelTo(Carbon::parse('2026-10-24 10:00:00'));
        $tenant = Tenant::query()->create(['name' => 'Espace '.$slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);
        $customer = Customer::query()->create(['name' => 'Cliente '.$slug, 'phone' => '620000001', 'address' => 'Adresse '.$slug, 'status' => 'Actif']);
        Customer::query()->create(['reference' => 'UNRELATED-'.$slug, 'name' => 'Sans lien']);
        $client = $customer;
        $tontine = Tontine::query()->create(['customer_id' => $client->id, 'reference' => 'TN-'.$slug, 'daily_amount' => 50000, 'start_date' => '2026-10-20', 'end_date' => '2026-10-26', 'status' => 'active']);

        return [$user, $customer, $tontine];
    }

    private function pay(Tontine $tontine, string $date, bool $voided = false): void
    {
        $day = TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => $date, 'amount' => 50000]);
        TontinePayment::query()->create(['tontine_id' => $tontine->id, 'tontine_day_id' => $day->id, 'amount' => 50000, 'payment_date' => '2026-10-24', 'recorded_by' => auth()->id(), 'voided_at' => $voided ? now() : null]);
    }
}
