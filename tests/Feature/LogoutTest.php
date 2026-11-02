<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_is_logged_out_and_session_is_cleared(): void
    {
        $user = UserFactory::new()->make(['id' => 1, 'remember_token' => null]);

        $response = $this->actingAs($user)
            ->withSession(['private_data' => 'secret'])
            ->post(route('logout'));

        $response->assertRedirectToRoute('login');
        $response->assertSessionMissing('private_data');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->post(route('logout'))->assertRedirectToRoute('login');

        $this->assertGuest();
    }

    public function test_dashboard_displays_logout_form(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Alpha', 'slug' => 'alpha', 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => '620000001', 'is_active' => true]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('action="'.route('logout').'"', false);
        $response->assertSee('name="_token"', false);
    }
}
