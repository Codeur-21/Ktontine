<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Livewire\Auth\Register;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_command_creates_a_platform_admin_without_phone(): void
    {
        $this->artisan('make:filament-user', $this->commandData())->assertExitCode(0);
        $user = User::query()->sole();
        $this->assertNull($user->phone);
        $this->assertTrue($user->isPlatformAdmin());
        $this->assertTrue(Hash::check('SecurePass1!', $user->password));
    }

    public function test_multiple_accounts_can_have_null_phone_in_database(): void
    {
        $this->artisan('make:filament-user', $this->commandData())->assertExitCode(0);
        $this->artisan('filament:user', [...$this->commandData(), '--email' => 'second@example.com'])->assertExitCode(0);
        $this->assertSame(2, User::query()->whereNull('phone')->count());
    }

    public function test_registration_still_requires_phone(): void
    {
        Livewire::test(Register::class)->call('register')->assertHasErrors(['phone' => 'required']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_web_admin_can_create_a_tenant_user_with_phone(): void
    {
        $this->admin();
        $tenant = Tenant::query()->create(['name' => 'Espace', 'slug' => 'espace']);
        Livewire::test(CreateUser::class)->fillForm([
            'name' => 'Agent', 'email' => 'agent@example.com', 'phone' => '620000003',
            'password' => 'SecurePass1!', 'tenant_id' => $tenant->id, 'role' => 'agent', 'is_active' => true,
        ])->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('users', ['email' => 'agent@example.com', 'phone' => '620000003', 'tenant_id' => $tenant->id]);
    }

    public function test_web_admin_gets_validation_for_missing_invalid_and_duplicate_phones(): void
    {
        $this->admin();
        $form = Livewire::test(CreateUser::class)->fillForm(['name' => 'Agent', 'email' => 'agent@example.com', 'password' => 'SecurePass1!', 'role' => 'agent']);
        $form->fillForm(['phone' => ''])->call('create')->assertHasFormErrors(['phone' => 'required']);
        $form->fillForm(['phone' => 'abcdefghi'])->call('create')->assertHasFormErrors(['phone' => 'digits']);
        $form->fillForm(['phone' => '699000001'])->call('create')->assertHasFormErrors(['phone' => 'unique']);
        $this->assertDatabaseMissing('users', ['email' => 'agent@example.com']);
    }

    public function test_edit_accepts_unchanged_phone_and_preserves_password(): void
    {
        $this->admin();
        $user = UserFactory::new()->create(['phone' => '620000004']);
        $password = $user->password;
        Livewire::test(EditUser::class, ['record' => $user->id])->fillForm(['name' => 'Nom modifié', 'password' => ''])->call('save')->assertHasNoFormErrors();
        $this->assertSame('620000004', $user->fresh()->phone);
        $this->assertSame($password, $user->fresh()->password);
    }

    /** @return array<string, string|bool> */
    private function commandData(): array
    {
        return ['--name' => 'Admin', '--email' => 'new@example.com', '--password' => 'SecurePass1!', '--panel' => 'workspace', '--no-interaction' => true];
    }

    private function admin(): void
    {
        $admin = UserFactory::new()->create(['phone' => '699000001', 'tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('workspace'));
    }
}
