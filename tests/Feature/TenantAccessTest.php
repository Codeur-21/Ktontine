<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Calendrier;
use App\Livewire\Client;
use App\Livewire\Cotisation;
use App\Livewire\Equipe;
use App\Livewire\Tontine;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TenantAccessTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('unavailableMemberships')]
    public function test_private_routes_reject_unavailable_membership(string $state): void
    {
        $owner = $this->owner();
        $this->revoke($owner, $state);

        foreach (['dashboard', 'clients', 'tontines', 'equipe', 'profile', 'abonnement', 'cotisations', 'parametres', 'calendrier', 'retards', 'rapports', 'backend'] as $route) {
            $this->actingAs($owner->fresh())->get(route($route))->assertForbidden();
        }
    }

    #[DataProvider('unavailableMemberships')]
    public function test_login_rejects_unavailable_membership(string $state): void
    {
        $owner = $this->owner();
        $this->revoke($owner, $state);

        Livewire::test(Login::class)->set('email', $owner->email)->set('password', 'SecurePass1!')
            ->call('login')->assertHasErrors('email');

        $this->assertGuest();
    }

    public static function unavailableMemberships(): array
    {
        return [
            'inactive user' => ['inactive_user'],
            'missing tenant' => ['missing_tenant'],
            'inactive tenant' => ['inactive_tenant'],
            'deleted tenant' => ['deleted_tenant'],
        ];
    }

    public function test_active_member_can_login_and_access_dashboard(): void
    {
        $owner = $this->owner();

        Livewire::test(Login::class)->set('email', $owner->email)->set('password', 'SecurePass1!')
            ->call('login')->assertHasNoErrors()->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($owner);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_platform_admin_can_login_through_filament_without_a_tenant(): void
    {
        $admin = UserFactory::new()->create(['tenant_id' => null, 'phone' => null, 'role' => 'owner', 'is_active' => true, 'password' => 'SecurePass1!']);
        Filament::setCurrentPanel(Filament::getPanel('workspace'));
        Livewire::test(\App\Filament\Auth\Login::class)
            ->fillForm(['email' => $admin->email, 'password' => 'SecurePass1!'])
            ->call('authenticate')->assertHasNoFormErrors();
        $this->assertAuthenticatedAs($admin);
        Auth::forgetGuards();
        $this->get('/workspace')->assertOk();
    }

    public function test_tenant_owners_cannot_access_global_administration(): void
    {
        $owner = $this->owner();

        $this->assertFalse($owner->canAccessPanel(Panel::make()->id('workspace')));
        $this->actingAs($owner)->get('/workspace')->assertForbidden();
        $this->actingAs($owner)->get('/workspace/tenants')->assertForbidden();
        $this->actingAs($owner)->get('/workspace/users')->assertForbidden();
    }

    public function test_only_active_platform_owner_without_tenant_can_access_global_panel(): void
    {
        $platformOwner = UserFactory::new()->make(['tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        $panel = Panel::make()->id('workspace');

        $this->assertTrue($platformOwner->canAccessPanel($panel));
        $platformOwner->is_active = false;
        $this->assertFalse($platformOwner->canAccessPanel($panel));
        $platformOwner->is_active = true;
        $platformOwner->role = 'agent';
        $this->assertFalse($platformOwner->canAccessPanel($panel));
    }

    public function test_suspended_tenant_can_still_logout(): void
    {
        $owner = $this->owner();
        $this->revoke($owner, 'inactive_tenant');

        $this->actingAs($owner)->post(route('logout'))->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_tenant_suspension_blocks_actions_on_already_open_components(): void
    {
        $owner = $this->owner();
        foreach ([[Client::class, 'register'], [Tontine::class, 'save'], [Cotisation::class, 'save'], [Equipe::class, 'save'], [Calendrier::class, 'selectTontine']] as [$component, $action]) {
            $owner->tenant()->update(['is_active' => true]);
            $page = Livewire::actingAs($owner)->test($component);
            $owner->tenant()->update(['is_active' => false]);
            $arguments = $action === 'selectTontine' ? [999] : [];
            $page->call($action, ...$arguments)->assertForbidden();
        }

        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('tontines', 0);
        $this->assertDatabaseCount('tontine_payments', 0);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_livewire_update_rechecks_tenant_after_the_initial_page_load(): void
    {
        $owner = $this->owner();
        $response = $this->actingAs($owner)->get(route('dashboard'))->assertOk();
        preg_match('/wire:snapshot="([^"]+)"/', $response->getContent(), $matches);
        $this->assertArrayHasKey(1, $matches);
        $snapshot = html_entity_decode($matches[1], ENT_QUOTES);
        $owner->tenant()->update(['is_active' => false]);

        $this->withHeader('X-Livewire', 'true')->postJson(Livewire::getUpdateUri(), [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => [],
                'calls' => [['path' => '', 'method' => '$refresh', 'params' => []]],
            ]],
        ])->assertForbidden();
    }

    public function test_tenant_owner_and_members_relations_only_return_their_users(): void
    {
        $owner = $this->owner();
        $otherTenant = Tenant::query()->create(['name' => 'Beta', 'slug' => 'beta', 'is_active' => true]);
        UserFactory::new()->create(['tenant_id' => $otherTenant->id, 'phone' => '620000002', 'role' => 'owner']);

        $this->assertSame($owner->id, $owner->tenant->owner->id);
        $this->assertSame([$owner->id], $owner->tenant->members()->pluck('id')->all());
    }

    public function test_guest_cannot_access_legacy_backend(): void
    {
        $this->get('/backend')->assertRedirectToRoute('login');
        $this->get('/connexion')->assertRedirect('/login');
    }

    private function owner(): User
    {
        $tenant = Tenant::query()->create(['name' => 'Alpha', 'slug' => 'alpha', 'is_active' => true]);

        return UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => '620000001', 'role' => 'owner', 'is_active' => true, 'password' => 'SecurePass1!']);
    }

    private function revoke(User $owner, string $state): void
    {
        match ($state) {
            'inactive_user' => $owner->update(['is_active' => false]),
            'missing_tenant' => DB::table('users')->where('id', $owner->id)->update(['tenant_id' => null]),
            'inactive_tenant' => $owner->tenant()->update(['is_active' => false]),
            'deleted_tenant' => $owner->tenant()->delete(),
        };
    }
}
