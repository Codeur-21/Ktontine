<?php

namespace Tests\Feature;

use App\Livewire\Client;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_list_and_search_never_expose_another_tenant(): void
    {
        $owner = $this->owner('alpha');
        $this->actingAs($owner);
        Customer::query()->create(['name' => 'Cliente Alpha', 'phone' => '620000001', 'address' => 'Conakry', 'status' => 'Actif']);
        $other = $this->owner('beta');
        $this->actingAs($other);
        Customer::query()->create(['name' => 'Cliente Secrete', 'phone' => '620000002', 'address' => 'Conakry', 'status' => 'Actif']);

        Livewire::actingAs($owner)->test(Client::class)->assertSee('Cliente Alpha')->assertDontSee('Cliente Secrete')
            ->set('search', 'Actif')->assertSee('Cliente Alpha')->assertDontSee('Cliente Secrete')
            ->set('search', '620000002')->assertDontSee('Cliente Secrete');
    }

    public function test_created_customer_is_attached_to_authenticated_tenant(): void
    {
        $owner = $this->owner('alpha');

        Livewire::actingAs($owner)->test(Client::class)->set('name', 'Nouvelle cliente')
            ->set('phone', '620000001')->set('address', 'Conakry')->call('register')->assertHasNoErrors();

        $this->assertDatabaseHas('customers', ['name' => 'Nouvelle cliente', 'tenant_id' => $owner->tenant_id]);
    }

    public function test_generated_reference_has_exactly_seven_characters_after_prefix(): void
    {
        $owner = $this->owner('alpha');
        $this->actingAs($owner);
        $customer = Customer::query()->create(['name' => 'Client']);
        $this->assertMatchesRegularExpression('/^CL-[A-Z0-9]{7}$/', $customer->reference);
    }

    public function test_reference_generation_does_not_reuse_foreign_or_soft_deleted_reference(): void
    {
        $alpha = $this->owner('alpha');
        $this->actingAs($alpha);
        $existing = Customer::query()->create(['name' => 'Ancien client', 'reference' => 'CL-ABC1234']);
        $existing->delete();
        $beta = $this->owner('beta');
        $this->actingAs($beta);
        $index = 0;
        Str::createRandomStringsUsing(function (int $length) use (&$index): string {
            return ['ABC1234', 'XYZ5678'][$index++];
        });
        try {
            $customer = Customer::query()->create(['name' => 'Client']);
            $this->assertSame('CL-XYZ5678', $customer->reference);
            $this->assertSame(2, $index);
        } finally {
            Str::createRandomStringsNormally();
        }
    }

    private function owner(string $slug): User
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);

        return UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => 'owner', 'is_active' => true]);
    }
}
