<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPaymentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_tenant_sees_one_month_trial_and_annual_price_without_fake_payments(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(10, 0));
        $this->owner();
        $this->get(route('abonnement'))->assertOk()->assertViewHas('state', 'trial')
            ->assertSee('200 000')->assertSee('GNF / an')->assertSee('29/11/2026')
            ->assertSee('Aucun paiement confirmé')->assertDontSee('GNF / mois');
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_preview_query_is_ignored_and_navigation_remains_available(): void
    {
        $this->owner();
        $this->get(route('abonnement', ['preview' => 'expired']))->assertOk()->assertViewHas('state', 'trial')
            ->assertDontSee('Aperçu de l’interface')->assertDontSee('Quitter l’aperçu')
            ->assertSee('href="/clients"', false)->assertSee(route('dashboard'), false);
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('abonnement'))->assertViewHas('state', 'trial');
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_trial_ends_one_calendar_month_after_tenant_creation(): void
    {
        $this->travelTo(now()->setDate(2026, 1, 31)->setTime(10, 0));
        $this->owner();
        $this->travelTo(now()->setDate(2026, 2, 28)->setTime(10, 1));
        $this->get(route('abonnement'))->assertOk()->assertViewHas('state', 'expired')->assertSee('28/02/2026')
            ->assertSee('Votre mois gratuit est terminé.')->assertSee('votre navigation reste accessible.')
            ->assertSee('href="/clients"', false)->assertDontSee('payment-only');
    }

    public function test_foreign_subscription_cannot_activate_the_current_tenant(): void
    {
        $alpha = $this->owner();
        $foreign = Tenant::query()->create(['name' => 'beta', 'slug' => 'beta', 'is_active' => true]);
        $beta = UserFactory::new()->create(['tenant_id' => $foreign->id, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($beta);
        Subscription::query()->create(['tenant_id' => $foreign->id, 'status' => 'active', 'amount' => 200000, 'currency' => 'GNF', 'ends_at' => now()->addYear()]);
        $this->actingAs($alpha)->get(route('abonnement'))->assertViewHas('state', 'trial');
    }

    public function test_guests_and_suspended_tenants_cannot_access_payment_page(): void
    {
        $this->get(route('abonnement'))->assertRedirectToRoute('login');
        $owner = $this->owner();
        $owner->tenant()->update(['is_active' => false]);
        $this->get(route('abonnement'))->assertForbidden();
    }

    private function owner(): User
    {
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => true]);
        $owner = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($owner);

        return $owner;
    }
}
