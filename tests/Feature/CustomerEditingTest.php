<?php

namespace Tests\Feature;

use App\Livewire\Client;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Tontine;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerEditingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_edited_without_changing_reference_tenant_or_tontines(): void
    {
        $user = $this->owner('alpha');
        $customer = Customer::query()->create(['name' => 'Nom initial', 'phone' => '620000001', 'address' => 'Conakry']);
        $cycle = Tontine::query()->create(['customer_id' => $customer->id, 'reference' => 'TN-EDIT', 'daily_amount' => 10000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
        Livewire::actingAs($user)->test(Client::class)->assertSee('Modifier')
            ->call('edit', $customer->id)->assertSet('editingId', $customer->id)->assertSet('name', 'Nom initial')
            ->assertSet('phone', '620000001')->assertSet('address', 'Conakry')->assertSet('status', 'Actif')
            ->assertSee('Modifier le client')->assertSee('Enregistrer les modifications')
            ->set('name', 'Fatou Camara')->set('phone', '621123456')->set('address', 'Kindia')->set('status', 'Inactif')
            ->call('register')->assertHasNoErrors()->assertSet('editingId', null)
            ->assertSee('Client modifié')->assertSee('Les informations du client ont été mises à jour.');
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'tenant_id' => $user->tenant_id, 'reference' => $customer->reference, 'name' => 'Fatou Camara', 'phone' => '621123456', 'address' => 'Kindia', 'status' => 'Inactif']);
        $this->assertDatabaseHas('tontines', ['id' => $cycle->id, 'customer_id' => $customer->id, 'daily_amount' => 10000]);
    }

    public function test_invalid_edit_keeps_form_open_and_does_not_write_changes(): void
    {
        $user = $this->owner('alpha');
        $customer = Customer::query()->create(['name' => 'Nom initial', 'phone' => '620000001', 'address' => 'Conakry']);
        Livewire::actingAs($user)->test(Client::class)->call('edit', $customer->id)
            ->set('name', '')->set('phone', '12')->set('address', '')->set('status', 'Administrateur')
            ->call('register')->assertHasErrors(['name', 'phone', 'address', 'status'])
            ->assertSet('editingId', $customer->id)->assertSee('Le téléphone doit contenir exactement 9 chiffres.')
            ->assertSee('Choisissez un statut valide : Actif ou Inactif.');
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Nom initial', 'phone' => '620000001', 'status' => 'Actif']);
    }

    public function test_cancel_discards_edit_and_next_save_creates_a_new_customer(): void
    {
        $user = $this->owner('alpha');
        $customer = Customer::query()->create(['name' => 'Nom initial']);
        Livewire::actingAs($user)->test(Client::class)->call('edit', $customer->id)->set('name', 'Brouillon')
            ->call('annuler')->assertSet('editingId', null)->assertSet('name', '')->assertSet('status', 'Actif')
            ->set('name', 'Nouveau client')->set('phone', '620000002')->set('address', 'Conakry')
            ->call('register')->assertHasNoErrors();
        $this->assertDatabaseCount('customers', 2);
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Nom initial']);
    }

    public function test_foreign_and_deleted_customers_cannot_be_loaded_or_saved(): void
    {
        $this->owner('foreign');
        $foreign = Customer::query()->create(['name' => 'Client étranger']);
        $user = $this->owner('alpha');
        $deleted = Customer::query()->create(['name' => 'Ancien client']);
        $deleted->delete();
        Livewire::actingAs($user)->test(Client::class)->call('edit', $foreign->id)->assertNotFound();
        Livewire::actingAs($user)->test(Client::class)->call('edit', $deleted->id)->assertNotFound();
        $customer = Customer::query()->create(['name' => 'À supprimer', 'phone' => '620000001', 'address' => 'Conakry']);
        $editor = Livewire::actingAs($user)->test(Client::class)->call('edit', $customer->id);
        $customer->delete();
        $editor->set('name', 'Changement')->call('register')->assertNotFound();
        $this->assertSoftDeleted($customer);
    }

    public function test_suspended_tenant_cannot_save_an_open_customer_edit(): void
    {
        $user = $this->owner('alpha');
        $customer = Customer::query()->create(['name' => 'Nom initial', 'phone' => '620000001', 'address' => 'Conakry']);
        $editor = Livewire::actingAs($user)->test(Client::class)->call('edit', $customer->id);
        $user->tenant()->update(['is_active' => false]);
        $editor->call('register')->assertForbidden();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Nom initial']);
    }

    private function owner(string $slug): User
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }
}
