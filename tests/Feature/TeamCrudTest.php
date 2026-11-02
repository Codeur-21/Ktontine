<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Equipe;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\UserFactory;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class TeamCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_creates_a_member_in_their_tenant(): void
    {
        $owner = $this->owner('alpha');

        Livewire::actingAs($owner)->test(Equipe::class)
            ->set('name', 'Membre Alpha')->set('email', 'member@example.com')->set('phone', '622000001')
            ->set('password', 'SecurePass1!')->set('password_confirmation', 'SecurePass1!')
            ->call('save')->assertHasNoErrors()->assertSee('Le membre a été ajouté à votre équipe.');

        $member = User::query()->where('email', 'member@example.com')->sole();
        $this->assertSame($owner->tenant_id, $member->tenant_id);
        $this->assertSame('agent', $member->role);
        $this->assertTrue(Hash::check('SecurePass1!', $member->password));
        $this->assertFalse($member->canAccessPanel(Panel::make()->id('workspace')));
    }

    public function test_owner_updates_member_without_changing_password_or_tenant(): void
    {
        $owner = $this->owner('alpha');
        $member = $this->member($owner);
        $originalHash = $member->password;

        Livewire::actingAs($owner)->test(Equipe::class)->call('edit', $member->id)
            ->set('name', 'Nom modifié')->set('role', 'manager')->call('save')
            ->assertHasNoErrors()->assertSee('Le membre a été mis à jour.');

        $this->assertDatabaseHas('users', ['id' => $member->id, 'name' => 'Nom modifié', 'role' => 'manager', 'tenant_id' => $owner->tenant_id]);
        $this->assertSame($originalHash, $member->fresh()->password);
    }

    public function test_owner_deletes_member_and_their_sessions(): void
    {
        $owner = $this->owner('alpha');
        $member = $this->member($owner);
        DB::table('sessions')->insert(['id' => 'member-session', 'user_id' => $member->id, 'payload' => '', 'last_activity' => now()->timestamp]);

        Livewire::actingAs($owner)->test(Equipe::class)->call('confirmDelete', $member->id)
            ->call('delete')->assertHasNoErrors()->assertSee('Le membre a été supprimé de votre équipe.');

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
        $this->assertDatabaseMissing('sessions', ['id' => 'member-session']);
    }

    public function test_members_of_other_tenants_are_hidden_and_cannot_be_edited_or_deleted(): void
    {
        $owner = $this->owner('alpha');
        $otherOwner = $this->owner('beta');
        $member = $this->member($otherOwner, 'secret@example.com');

        Livewire::actingAs($owner)->test(Equipe::class)->assertDontSee('secret@example.com')->set('search', 'secret')->assertDontSee('secret@example.com');
        Livewire::actingAs($owner)->test(Equipe::class)->call('edit', $member->id)->assertNotFound();
        Livewire::actingAs($owner)->test(Equipe::class)->call('confirmDelete', $member->id)->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $member->id, 'tenant_id' => $otherOwner->tenant_id]);
    }

    public function test_agents_and_managers_cannot_manage_team(): void
    {
        $owner = $this->owner('alpha');
        foreach (['agent', 'manager'] as $role) {
            $member = $this->member($owner, $role.'@example.com', $role);
            Livewire::actingAs($member)->test(Equipe::class)->call('save')->assertForbidden();
            Livewire::actingAs($member)->test(Equipe::class)->call('edit', $owner->id)->assertForbidden();
            Livewire::actingAs($member)->test(Equipe::class)->call('confirmDelete', $owner->id)->assertForbidden();
            Livewire::actingAs($member)->test(Equipe::class)->call('delete')->assertForbidden();
        }
    }

    public function test_owner_account_is_protected(): void
    {
        $owner = $this->owner('alpha');

        Livewire::actingAs($owner)->test(Equipe::class)->call('edit', $owner->id)->assertForbidden();
        Livewire::actingAs($owner)->test(Equipe::class)->call('confirmDelete', $owner->id)->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'role' => 'owner']);
    }

    public function test_invalid_fields_and_owner_role_are_rejected(): void
    {
        $owner = $this->owner('alpha');

        Livewire::actingAs($owner)->test(Equipe::class)->set('email', $owner->email)->set('phone', $owner->phone)
            ->set('role', 'owner')->call('save')->assertHasErrors(['name', 'email', 'phone', 'password', 'role']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_deactivation_revokes_sessions_and_prevents_login(): void
    {
        $owner = $this->owner('alpha');
        $member = $this->member($owner);
        DB::table('sessions')->insert(['id' => 'disabled-session', 'user_id' => $member->id, 'payload' => '', 'last_activity' => now()->timestamp]);

        Livewire::actingAs($owner)->test(Equipe::class)->call('edit', $member->id)->set('isActive', false)->call('save')->assertHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['id' => 'disabled-session']);
        $this->assertFalse($member->fresh()->is_active);
        auth()->logout();
        Livewire::test(Login::class)->set('email', $member->email)->set('password', 'SecurePass1!')->call('login')->assertHasErrors('email');
        $this->assertGuest();
    }

    private function owner(string $slug): User
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);

        return UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => 'owner', 'is_active' => true]);
    }

    private function member(User $owner, string $email = 'member@example.com', string $role = 'agent'): User
    {
        return UserFactory::new()->create(['tenant_id' => $owner->tenant_id, 'email' => $email, 'phone' => fake()->unique()->numerify('62#######'), 'role' => $role, 'is_active' => true, 'password' => 'SecurePass1!']);
    }
}
