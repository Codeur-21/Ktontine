<?php

namespace Tests\Feature;

use App\Livewire\Auth\Register;
use App\Models\Scopes\TenantScope;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\PlatformNotifications;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformDatabaseNotificationsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
        $this->travelTo(now()->setDate(2026, 10, 30)->setTime(10, 0));
        Filament::setCurrentPanel(Filament::getPanel('workspace'));
    }

    public function test_new_registration_notifies_only_active_panel_administrators_after_commit(): void
    {
        $admin = $this->admin();
        $second = $this->admin();
        $disabled = $this->admin(false);
        auth()->logout();
        Livewire::test(Register::class)->set('name', 'Abdoulaye')->set('business_name', 'Mon espace')
            ->set('phone', '626427442')->set('email', 'owner@example.com')
            ->set('password', 'SecurePass1!')->set('password_confirmation', 'SecurePass1!')->set('terms', true)
            ->call('register')->assertHasNoErrors()->assertRedirect(route('login'));
        $this->assertSame('Nouvelle inscription tontinier', $admin->notifications()->sole()->data['title']);
        $this->assertSame('filament', $admin->notifications()->sole()->data['format']);
        $this->assertStringContainsString('Mon espace', $admin->notifications()->sole()->data['body']);
        $this->assertSame(1, $second->notifications()->count());
        $this->assertSame(0, $disabled->notifications()->count());
        $this->assertSame(0, User::query()->where('email', 'owner@example.com')->firstOrFail()->notifications()->count());
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_rolled_back_registration_creates_no_notification(): void
    {
        $admin = $this->admin();
        DB::beginTransaction();
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => true]);
        UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner']);
        $this->assertSame(0, $admin->notifications()->count());
        DB::rollBack();
        $this->assertSame(0, $admin->notifications()->count());
        $this->assertDatabaseCount('platform_notification_events', 0);
    }

    public function test_subscription_creation_and_meaningful_updates_notify_admins_without_changing_billing(): void
    {
        $admin = $this->admin();
        $tenant = $this->tenant('alpha');
        $admin->notifications()->delete();
        $subscription = $this->subscription($tenant, now()->addYear());
        $this->assertSame('Nouvel abonnement', $admin->notifications()->sole()->data['title']);
        $subscription->update(['ends_at' => now()->addYears(2)]);
        $this->assertSame(2, $admin->notifications()->count());
        $subscription->save();
        $this->assertSame(2, $admin->notifications()->count());
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertSame(0, User::query()->where('tenant_id', $tenant->id)->firstOrFail()->notifications()->count());
    }

    public function test_upcoming_and_expired_alerts_are_saved_once_even_after_reading_and_deletion(): void
    {
        $admin = $this->admin();
        $tenant = $this->tenant('alpha');
        $subscription = $this->subscription($tenant, now()->addDays(3));
        $admin->notifications()->delete();
        $this->artisan('platform:notify-expirations')->assertSuccessful();
        $this->assertSame('Un abonnement arrive à échéance', $admin->notifications()->sole()->data['title']);
        $admin->notifications()->sole()->markAsRead();
        $this->artisan('platform:notify-expirations')->assertSuccessful();
        $this->assertSame(1, $admin->notifications()->count());
        $admin->notifications()->delete();
        $this->artisan('platform:notify-expirations')->assertSuccessful();
        $this->assertSame(0, $admin->notifications()->count());
        $this->travel(4)->days();
        $this->artisan('platform:notify-expirations')->assertSuccessful();
        $this->assertSame('Abonnement expiré', $admin->notifications()->sole()->data['title']);
        $this->assertSame('active', $subscription->fresh()->status);
    }

    public function test_newest_renewal_prevents_alerting_on_historical_expired_subscriptions(): void
    {
        $admin = $this->admin();
        $tenant = $this->tenant('alpha');
        $this->subscription($tenant, now()->subDays(2));
        $this->subscription($tenant, now()->addYear());
        $admin->notifications()->delete();
        $this->artisan('platform:notify-expirations')->assertSuccessful();
        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_trial_without_subscription_and_explicit_trial_deadline_are_monitored_across_tenants(): void
    {
        $admin = $this->admin();
        $alpha = $this->tenant('alpha');
        $alpha->forceFill(['created_at' => now()->subMonthNoOverflow()->subDay()])->save();
        $beta = $this->tenant('beta');
        Subscription::query()->create(['tenant_id' => $beta->id, 'status' => 'trialing', 'trial_ends_at' => now()->addDays(2), 'amount' => 200000, 'currency' => 'GNF']);
        $this->tenant('fresh');
        $admin->notifications()->delete();
        auth()->logout();
        $this->artisan('platform:notify-expirations')->assertSuccessful();
        $titles = $admin->notifications()->get()->pluck('data.title')->all();
        $this->assertCount(2, $titles);
        $this->assertContains('Essai gratuit terminé', $titles);
        $this->assertContains('Un essai gratuit arrive à échéance', $titles);
        $this->assertSame(1, Subscription::withoutGlobalScope(TenantScope::class)->count());
    }

    public function test_suspended_tenants_are_not_monitored_and_invalid_deadlines_are_ignored(): void
    {
        $admin = $this->admin();
        $inactive = $this->tenant('inactive');
        $this->subscription($inactive, now()->subDay());
        $inactive->update(['is_active' => false]);
        $missingDate = $this->tenant('no-date');
        Subscription::query()->create(['tenant_id' => $missingDate->id, 'status' => 'active', 'amount' => 200000, 'currency' => 'GNF']);
        $admin->notifications()->delete();
        $this->artisan('platform:notify-expirations')->assertSuccessful();
        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_repeated_registration_event_and_team_member_creation_do_not_duplicate_alerts(): void
    {
        $admin = $this->admin();
        $tenant = $this->tenant('alpha');
        $owner = User::query()->where('tenant_id', $tenant->id)->firstOrFail();
        app(PlatformNotifications::class)->ownerRegistered($owner);
        UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'agent']);
        $this->assertSame(1, $admin->notifications()->count());
    }

    private function admin(bool $active = true): User
    {
        $admin = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => $active]);
        $this->actingAs($admin);

        return $admin;
    }

    private function tenant(string $slug): Tenant
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);

        return $tenant;
    }

    private function subscription(Tenant $tenant, mixed $end): Subscription
    {
        return Subscription::query()->create(['tenant_id' => $tenant->id, 'status' => 'active', 'amount' => 200000, 'currency' => 'GNF', 'starts_at' => now()->subYear(), 'ends_at' => $end]);
    }
}
