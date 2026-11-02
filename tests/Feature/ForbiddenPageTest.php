<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForbiddenPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_member_sees_explanation_and_dashboard_link_on_workspace(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Espace', 'slug' => 'espace', 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => '620000001', 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user)->get('/workspace')->assertForbidden()
            ->assertSee('Cet espace est réservé à l’administration.')
            ->assertSee('Revenir à mon tableau de bord')->assertSee(route('dashboard'), false);
        $this->actingAs($user)->get('/workspace/subscriptions')->assertForbidden()
            ->assertSee('Cet espace est réservé à l’administration.');
    }

    public function test_suspended_user_gets_generic_explanation_without_another_forbidden_dashboard_link(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Espace', 'slug' => 'espace', 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => '620000001', 'is_active' => false]);
        $this->actingAs($user)->get(route('dashboard'))->assertForbidden()
            ->assertSee('Cette page ne vous est pas accessible.')->assertSee('Retour à l’accueil')
            ->assertDontSee('Revenir à mon tableau de bord');
    }
}
