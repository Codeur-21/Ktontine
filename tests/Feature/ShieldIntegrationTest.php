<?php

namespace Tests\Feature;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShieldIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_badges_count_resources_only_for_platform_administrators(): void
    {
        $admin = $this->admin();
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => true]);
        $owner = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        Subscription::query()->create(['tenant_id' => $tenant->id, 'status' => 'active', 'amount' => 200000, 'currency' => 'GNF']);
        $this->assertSame('1', TenantResource::getNavigationBadge());
        $this->assertSame('2', UserResource::getNavigationBadge());
        $this->assertSame('1', SubscriptionResource::getNavigationBadge());
        $this->actingAs($owner);
        $this->assertNull(TenantResource::getNavigationBadge());
        $this->assertNull(UserResource::getNavigationBadge());
        $this->assertNull(SubscriptionResource::getNavigationBadge());
    }

    public function test_central_users_display_shield_roles_while_tenant_users_keep_business_roles(): void
    {
        $this->admin();
        $central = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner']);
        $role = Role::query()->create(['name' => 'auditeur', 'guard_name' => 'web']);
        $central->assignRole($role);
        $unassigned = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner']);
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => true]);
        $owner = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner']);
        $owner->assignRole($role);
        Livewire::test(ListUsers::class)
            ->assertTableColumnStateSet('role', ['auditeur'], $central)
            ->assertTableColumnStateSet('role', ['Aucun rôle Shield'], $unassigned)
            ->assertTableColumnStateSet('role', 'owner', $owner);
        $this->get(UserResource::getUrl('view', ['record' => $central]))
            ->assertOk()->assertSee('auditeur')->assertDontSee('Propriétaire');
    }

    public function test_platform_admin_can_open_roles_and_create_a_role(): void
    {
        $this->admin();
        $this->get(RoleResource::getUrl())->assertOk()->assertSee('Rôles et permissions');
        Livewire::test(CreateRole::class)->fillForm(['name' => 'auditeur', 'guard_name' => 'web'])
            ->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('roles', ['name' => 'auditeur', 'guard_name' => 'web']);
    }

    public function test_permissions_can_be_generated_for_the_registered_panel(): void
    {
        $this->artisan('shield:generate', ['--all' => true, '--option' => 'permissions', '--panel' => 'workspace', '--no-interaction' => true])->assertExitCode(0);
        $this->assertDatabaseHas('permissions', ['name' => 'ViewAny:User', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'ViewAny:Role', 'guard_name' => 'web']);
    }

    public function test_admin_can_assign_shield_roles_to_a_central_administrator(): void
    {
        $this->admin();
        $target = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true, 'phone' => '620000001']);
        $role = Role::query()->create(['name' => 'administration', 'guard_name' => 'web']);
        Livewire::test(EditUser::class, ['record' => $target->id])->fillForm(['roles' => [$role->id]])
            ->call('save')->assertHasNoFormErrors();
        $this->assertTrue($target->fresh()->hasRole('administration'));
    }

    public function test_tenant_owners_cannot_enter_shield_even_with_a_super_admin_named_role(): void
    {
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => true]);
        $owner = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $role = Role::query()->create(['name' => 'super_admin', 'guard_name' => 'web']);
        $permission = Permission::query()->create(['name' => 'ViewAny:Role', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $owner->assignRole($role);
        $this->actingAs($owner)->get('/workspace/shield/roles')->assertForbidden();
        $this->get('/workspace/shield/roles/create')->assertForbidden();
    }

    public function test_guests_and_disabled_platform_admins_cannot_manage_roles(): void
    {
        $this->get('/workspace/shield/roles')->assertRedirect();
        $admin = $this->admin();
        $admin->update(['is_active' => false]);
        $this->get('/workspace/shield/roles')->assertForbidden();
    }

    private function admin(): User
    {
        $admin = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('workspace'));

        return $admin;
    }
}
