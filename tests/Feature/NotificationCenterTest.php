<?php

namespace Tests\Feature;

use App\Livewire\NotificationCenter;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Tontine;
use App\Models\TontineDay;
use App\Models\TontinePayment;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_arrears_update_after_payment_and_reappear_after_cancellation(): void
    {
        [$user, $tontine] = $this->workspace('alpha');
        $page = Livewire::actingAs($user)->test(NotificationCenter::class)
            ->assertSee('2 journée(s) à régulariser.')->assertSee('Fin de tontine proche');
        foreach (['2026-10-26', '2026-10-27'] as $date) {
            $day = TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => $date, 'amount' => 50000]);
            TontinePayment::query()->create(['tontine_id' => $tontine->id, 'tontine_day_id' => $day->id, 'amount' => 50000, 'payment_date' => '2026-10-28', 'recorded_by' => $user->id]);
        }
        $page->dispatch('payment-updated')->assertDontSee('Paiements en retard')->assertDontSee('Cotisation enregistrée');
        TontinePayment::query()->first()->update(['voided_at' => now()]);
        $page->call('$refresh')->assertSee('1 journée(s) à régulariser.')->assertSee('Cotisation annulée');
    }

    public function test_notifications_and_read_state_are_isolated_between_users(): void
    {
        [$alpha] = $this->workspace('alpha');
        [$beta] = $this->workspace('beta');
        $page = Livewire::actingAs($alpha)->test(NotificationCenter::class)
            ->assertSee('Client alpha')->assertDontSee('Client beta')
            ->call('markAllRead')->assertViewHas('unreadCount', 0);
        Livewire::actingAs($alpha)->test(NotificationCenter::class)->assertViewHas('unreadCount', 0);
        Livewire::actingAs($beta)->test(NotificationCenter::class)->assertDontSee('Client alpha')
            ->assertViewHas('unreadCount', fn ($count) => $count > 0);
    }

    public function test_new_day_reopens_read_arrears_and_closed_cycles_are_not_alerted(): void
    {
        [$user, $tontine] = $this->workspace('alpha');
        $page = Livewire::actingAs($user)->test(NotificationCenter::class)->call('markAllRead')->assertViewHas('unreadCount', 0);
        $this->travel(1)->days();
        $page->call('$refresh')->assertSee('3 journée(s) à régulariser.')->assertViewHas('unreadCount', 1);
        $tontine->update(['status' => 'closed']);
        $page->call('$refresh')->assertDontSee('Paiements en retard')->assertDontSee('Fin de tontine proche');
    }

    public function test_guest_and_suspended_users_cannot_read_notifications(): void
    {
        Livewire::test(NotificationCenter::class)->assertForbidden();
        [$user] = $this->workspace('alpha');
        $user->tenant()->update(['is_active' => false]);
        Livewire::actingAs($user)->test(NotificationCenter::class)->assertForbidden();
    }

    public function test_trial_expiry_and_customer_content_are_displayed_safely(): void
    {
        [$user, $tontine] = $this->workspace('alpha');
        $tontine->customer->update(['name' => '<script>alert(1)</script>']);
        Subscription::query()->create(['tenant_id' => $user->tenant_id, 'status' => 'trialing', 'amount' => 200000, 'currency' => 'GNF', 'trial_ends_at' => '2026-10-30']);
        Livewire::actingAs($user)->test(NotificationCenter::class)->assertSee('Abonnement à vérifier')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_future_payments_do_not_cover_missing_past_days(): void
    {
        [$user, $tontine] = $this->workspace('alpha');
        $day = TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => '2026-10-29', 'amount' => 50000]);
        $payment = TontinePayment::query()->create(['tontine_id' => $tontine->id, 'tontine_day_id' => $day->id, 'amount' => 50000, 'payment_date' => '2026-10-28', 'recorded_by' => $user->id]);
        $page = Livewire::actingAs($user)->test(NotificationCenter::class)->assertSee('2 journée(s) à régulariser.')
            ->call('markAllRead')->assertViewHas('unreadCount', 0);
        $payment->update(['voided_at' => now()]);
        $page->call('$refresh')->assertSee('Cotisation annulée')->assertViewHas('unreadCount', 1);
    }

    public function test_dismissed_notification_stays_hidden_after_refresh_without_deleting_business_data(): void
    {
        [$user, $tontine] = $this->workspace('alpha');
        $page = Livewire::actingAs($user)->test(NotificationCenter::class)
            ->call('dismiss', 'late-'.$tontine->id.'-2026-10-28-2')->assertDontSee('Paiements en retard')
            ->assertSee('Fin de tontine proche');
        $page->call('$refresh')->assertDontSee('Paiements en retard');
        Livewire::actingAs($user)->test(NotificationCenter::class)->assertDontSee('Paiements en retard');
        $this->assertDatabaseHas('customers', ['id' => $tontine->customer_id]);
        $this->assertDatabaseHas('tontines', ['id' => $tontine->id]);
    }

    public function test_dismiss_all_only_hides_current_notifications_and_new_alerts_still_appear(): void
    {
        [$user] = $this->workspace('alpha');
        $page = Livewire::actingAs($user)->test(NotificationCenter::class)->call('dismissAll')
            ->assertViewHas('unreadCount', 0)->assertViewHas('items', fn ($items) => $items->isEmpty())
            ->assertSee('Vous êtes à jour.');
        $page->call('$refresh')->assertViewHas('items', fn ($items) => $items->isEmpty());
        $this->travel(1)->days();
        $page->call('$refresh')->assertViewHas('unreadCount', 1)->assertSee('3 journée(s) à régulariser.');
    }

    public function test_user_cannot_dismiss_another_tenants_notification(): void
    {
        [$alpha] = $this->workspace('alpha');
        [$beta, $foreign] = $this->workspace('beta');
        Livewire::actingAs($alpha)->test(NotificationCenter::class)
            ->call('dismiss', 'late-'.$foreign->id.'-2026-10-28-2')->assertNotFound();
        Livewire::actingAs($beta)->test(NotificationCenter::class)->assertSee('Paiements en retard');
    }

    public function test_creations_and_successful_payments_do_not_generate_notifications(): void
    {
        [$user, $tontine] = $this->workspace('alpha');
        $day = TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => '2026-10-26', 'amount' => 50000]);
        TontinePayment::query()->create(['tontine_id' => $tontine->id, 'tontine_day_id' => $day->id, 'amount' => 50000, 'payment_date' => '2026-10-28', 'recorded_by' => $user->id]);

        Livewire::actingAs($user)->test(NotificationCenter::class)
            ->assertDontSee('Nouveau client')->assertDontSee('Cotisation enregistrée')
            ->assertSee('Paiements en retard')->assertSee('Fin de tontine proche')
            ->assertViewHas('items', fn ($items) => $items->count() === 2 && $items->every(fn ($item) => $item['type'] === 'warning'));
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('tontine_payments', 1);
    }

    public function test_cancelling_the_confirmation_keeps_alerts_until_dismissal_is_confirmed(): void
    {
        [$user, $tontine] = $this->workspace('alpha');
        $page = Livewire::actingAs($user)->test(NotificationCenter::class)
            ->assertSee('clear-notifications-modal')->assertSee('Annuler')
            ->assertDontSee('wire:confirm=', false)
            ->assertViewHas('items', fn ($items) => $items->count() === 2);

        $page->call('$refresh')->assertViewHas('items', fn ($items) => $items->count() === 2);
        $page->call('dismissAll')->assertViewHas('items', fn ($items) => $items->isEmpty());
        $this->assertDatabaseHas('customers', ['id' => $tontine->customer_id]);
        $this->assertDatabaseHas('tontines', ['id' => $tontine->id]);
    }

    private function workspace(string $slug): array
    {
        $this->travelTo(now()->setDate(2026, 10, 28)->setTime(10, 0));
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);
        $customer = Customer::query()->create(['name' => 'Client '.$slug]);
        $tontine = Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-'.$slug, 'daily_amount' => 50000, 'start_date' => '2026-10-26', 'end_date' => '2026-10-30', 'status' => 'active']);

        return [$user, $tontine];
    }
}
