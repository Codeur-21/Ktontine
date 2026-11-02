<?php

namespace Tests\Feature;

use App\Filament\Pages\UserProfile;
use App\Livewire\Profile;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WorkspaceProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_save_only_own_profile_and_cancel_pending_changes(): void
    {
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $other = UserFactory::new()->create();
        $originalEmail = $other->email;
        Livewire::actingAs($user)->test(Profile::class)
            ->set('name', 'Abdoulaye Sylla')->set('email', 'tontinier@example.com')->set('phone', '626427442')
            ->call('saveProfile')->assertHasNoErrors()->assertRedirect(route('profile'));
        $this->assertSame('Abdoulaye Sylla', $user->fresh()->name);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame($tenant->id, $user->fresh()->tenant_id);
        $this->assertSame($originalEmail, $other->fresh()->email);
        Livewire::actingAs($user->fresh())->test(Profile::class)
            ->set('name', 'Brouillon')->call('cancelProfile')->assertSet('name', 'Abdoulaye Sylla');
    }

    public function test_tenant_profile_rejects_duplicate_email_invalid_phone_and_disabled_access(): void
    {
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $other = UserFactory::new()->create();
        $page = Livewire::actingAs($user)->test(Profile::class)
            ->set('email', $other->email)->set('phone', 'abc')->call('saveProfile')
            ->assertHasErrors(['email' => 'unique', 'phone' => 'digits']);
        $this->assertNotSame($other->email, $user->fresh()->email);
        $user->update(['is_active' => false]);
        $page->call('saveProfile')->assertForbidden();
    }

    public function test_admin_can_open_their_profile_without_data_changes(): void
    {
        $user = UserFactory::new()->create(['tenant_id' => null, 'name' => '<script>test</script>', 'role' => 'owner', 'is_active' => true]);
        $before = $user->fresh()->getAttributes();
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('workspace'));
        $this->get(UserProfile::getUrl())->assertOk()->assertSee('Mes informations')->assertSee('Mot de passe actuel')
            ->assertSee($user->email)->assertSee('&lt;script&gt;test&lt;/script&gt;', false)
            ->assertDontSee('<script>test</script>', false)->assertSee('wire:submit="saveProfile"', false)
            ->assertSee('profile-password_confirmation')->assertSee('Enregistrer la photo')
            ->assertSee('id="profile-avatar"', false)->assertSee('Changer la photo')->assertSee(asset('default-avatar.svg'), false);
        $this->assertSame($before, $user->fresh()->getAttributes());
        $this->get(route('filament.workspace.pages.dashboard'))->assertOk()->assertSee(UserProfile::getUrl(), false);
    }

    public function test_tenant_profile_displays_the_connected_user_without_saving_changes(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Mon espace', 'slug' => 'alpha', 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'name' => 'Mariam Barry', 'role' => 'owner', 'is_active' => true]);
        $before = $user->fresh()->getAttributes();
        $this->actingAs($user)->get(route('profile'))->assertOk()->assertSee('Mariam Barry')
            ->assertSee($user->email)->assertSee('Mon espace')->assertSee('Sécurité du compte')
            ->assertDontSee('Données d’exemple')->assertSee('user-profile-password_confirmation');
        $this->assertSame($before, $user->fresh()->getAttributes());
    }

    public function test_guests_and_tenant_users_cannot_access_the_admin_profile(): void
    {
        $this->get('/workspace/mon-profil')->assertRedirect();
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user)->get('/workspace/mon-profil')->assertForbidden();
    }

    public function test_avatar_is_saved_for_the_connected_admin_and_used_by_the_panel(): void
    {
        Storage::fake('public');
        $user = $this->admin();
        $other = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        Livewire::test(UserProfile::class)->set('avatar', UploadedFile::fake()->image('avatar.png', 160, 160))
            ->call('saveAvatar')->assertHasNoErrors()->assertRedirect(UserProfile::getUrl());
        $user->refresh();
        Storage::disk('public')->assertExists($user->avatar_path);
        $this->assertStringStartsWith('avatars/'.$user->id.'/', $user->avatar_path);
        $url = Storage::disk('public')->url($user->avatar_path);
        $this->assertSame($url, $user->getFilamentAvatarUrl());
        $this->assertSame(url($url), Filament::getUserAvatarUrl($user));
        $this->assertNull($other->fresh()->avatar_path);
        $this->get(UserProfile::getUrl())->assertOk()->assertSee($url, false);
        Livewire::test(UserProfile::class)->call('removeAvatar')->assertHasNoErrors();
        $this->assertSame(asset('default-avatar.svg'), $user->fresh()->getFilamentAvatarUrl());
    }

    public function test_invalid_or_oversized_avatar_is_not_saved(): void
    {
        Storage::fake('public');
        $user = $this->admin();
        Livewire::test(UserProfile::class)->set('avatar', UploadedFile::fake()->create('script.svg', 10, 'image/svg+xml'))
            ->call('saveAvatar')->assertHasErrors(['avatar']);
        Livewire::test(UserProfile::class)->set('avatar', UploadedFile::fake()->image('big.png')->size(2049))
            ->call('saveAvatar')->assertHasErrors(['avatar' => 'max']);
        $this->assertNull($user->fresh()->avatar_path);
        $this->assertSame([], Storage::disk('public')->allFiles('avatars'));
    }

    public function test_profile_changes_only_current_user_and_invalidates_changed_email_verification(): void
    {
        $user = $this->admin();
        $other = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        Livewire::test(UserProfile::class)->set('name', 'Mariam Barry')->set('email', 'mariam@example.com')->set('phone', '626000001')
            ->call('saveProfile')->assertHasNoErrors()->assertRedirect(UserProfile::getUrl());
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Mariam Barry', 'email' => 'mariam@example.com', 'phone' => '626000001', 'email_verified_at' => null]);
        $this->assertDatabaseHas('users', ['id' => $other->id, 'email' => $other->email]);
    }

    public function test_duplicate_email_and_invalid_phone_do_not_change_profile(): void
    {
        $user = $this->admin();
        $other = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        Livewire::test(UserProfile::class)->set('email', $other->email)->set('phone', 'abc')
            ->call('saveProfile')->assertHasErrors(['email' => 'unique', 'phone' => 'digits']);
        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_password_change_requires_current_password_and_confirmation(): void
    {
        $user = $this->admin();
        $page = Livewire::test(UserProfile::class)->set('currentPassword', 'WrongPass1!')
            ->set('password', 'NewSecurePass1!')->set('password_confirmation', 'NewSecurePass1!')
            ->call('changePassword')->assertHasErrors(['currentPassword']);
        $this->assertTrue(Hash::check('SecurePass1!', $user->fresh()->password));
        $page->set('currentPassword', 'SecurePass1!')->set('password', 'NewSecurePass1!')->set('password_confirmation', 'NewSecurePass1!')
            ->call('changePassword')->assertHasNoErrors()->assertSet('password', '')->assertSet('currentPassword', '');
        $this->assertTrue(Hash::check('NewSecurePass1!', $user->fresh()->password));
        $this->assertSame($user->fresh()->password, session('password_hash_web'));
    }

    public function test_suspended_admin_cannot_save_profile_or_avatar(): void
    {
        $user = $this->admin();
        $page = Livewire::test(UserProfile::class);
        $avatarPage = Livewire::test(UserProfile::class);
        $user->update(['is_active' => false]);
        $page->call('saveProfile')->assertForbidden();
        $avatarPage->call('removeAvatar')->assertForbidden();
    }

    private function admin(): User
    {
        $user = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true, 'password' => 'SecurePass1!']);
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('workspace'));

        return $user;
    }
}
