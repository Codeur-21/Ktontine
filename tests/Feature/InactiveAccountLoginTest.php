<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\Tenant;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InactiveAccountLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_panel_user_receives_an_eight_second_notification_after_correct_password(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('workspace'));
        $user = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => false, 'password' => 'Password123!']);
        Livewire::test(\App\Filament\Auth\Login::class)->fillForm(['email' => $user->email, 'password' => 'Password123!'])
            ->call('authenticate')->assertHasFormErrors(['email']);
        Notification::assertNotified(Notification::make()
            ->title('Votre compte est désactivé')
            ->body('Votre accès à l’administration est suspendu. Contactez un administrateur pour demander sa réactivation.')
            ->danger()->duration(8000));
        $this->assertGuest();
    }

    public function test_wrong_panel_password_does_not_reveal_inactive_account(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('workspace'));
        $user = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => false, 'password' => 'Password123!']);
        Livewire::test(\App\Filament\Auth\Login::class)->fillForm(['email' => $user->email, 'password' => 'wrong'])
            ->call('authenticate')->assertHasFormErrors(['email']);
        Notification::assertNotNotified('Votre compte est désactivé');
        $this->assertGuest();
    }

    public function test_inactive_account_shows_popup_only_after_correct_credentials(): void
    {
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'is_active' => false, 'password' => 'Password123!', 'phone' => '626427442']);
        Livewire::test(Login::class)->set('email', $user->email)->set('password', 'wrong')
            ->call('login')->assertNotDispatched('account-inactive')->assertHasErrors('email');
        Livewire::test(Login::class)->set('loginType', 'phone')->set('phone', $user->phone)->set('password', 'Password123!')
            ->call('login')->assertDispatched('account-inactive')->assertSet('password', '')->assertHasErrors('phone');
        $this->assertGuest();
    }

    public function test_suspended_tenant_shows_popup_and_active_user_still_logs_in(): void
    {
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => false]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'is_active' => true, 'password' => 'Password123!']);
        Livewire::test(Login::class)->set('email', $user->email)->set('password', 'Password123!')
            ->call('login')->assertDispatched('account-inactive');
        $this->assertGuest();
        $tenant->update(['is_active' => true]);
        Livewire::test(Login::class)->set('email', $user->email)->set('password', 'Password123!')
            ->call('login')->assertNotDispatched('account-inactive')->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
