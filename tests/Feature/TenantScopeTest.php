<?php

namespace Tests\Feature;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Livewire\Auth\Register;
use App\Models\Contribution;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Tontine;
use App\Models\TontineDay;
use App\Models\TontinePayment;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_active_business_model_filters_reads_by_authenticated_tenant(): void
    {
        $alpha = $this->dataset('alpha');
        $beta = $this->dataset('beta');
        $this->actingAs($alpha['user']);

        foreach (['customer', 'tontine', 'day', 'payment', 'subscription', 'contribution'] as $key) {
            $model = $alpha[$key];
            $this->assertSame([$model->id], $model->newQuery()->pluck('id')->all(), $key);
            $this->assertNull($model->newQuery()->find($beta[$key]->id), $key);
        }
    }

    public function test_guest_reads_are_empty_without_an_explicit_context(): void
    {
        $data = $this->dataset('alpha');
        auth()->logout();

        foreach (['customer', 'tontine', 'day', 'payment', 'subscription', 'contribution'] as $key) {
            $this->assertSame(0, $data[$key]->newQuery()->count(), $key);
        }
    }

    public function test_creations_get_the_current_tenant_without_receiving_it_from_the_form(): void
    {
        $data = $this->dataset('alpha');
        $client = Customer::query()->create(['reference' => 'CL-NEW', 'name' => 'Nouveau client']);

        $this->assertSame($data['tenant']->id, $client->tenant_id);
        $this->assertDatabaseHas('customers', ['id' => $client->id, 'tenant_id' => $data['tenant']->id]);
    }

    public function test_creation_with_another_tenant_id_is_rejected(): void
    {
        $alpha = $this->dataset('alpha');
        $beta = $this->dataset('beta');
        $this->actingAs($alpha['user']);
        try {
            Customer::query()->create(['tenant_id' => $beta['tenant']->id, 'reference' => 'FORGED', 'name' => 'Interdit']);
            $this->fail('A foreign tenant must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertDatabaseMissing('customers', ['reference' => 'FORGED']);
    }

    public function test_existing_tenant_cannot_be_changed_or_deleted_from_another_context(): void
    {
        $alpha = $this->dataset('alpha');
        $beta = $this->dataset('beta');
        $this->actingAs($alpha['user']);
        try {
            $alpha['customer']->update(['tenant_id' => $beta['tenant']->id]);
            $this->fail('Tenant transfer must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        try {
            $beta['customer']->delete();
            $this->fail('Foreign instance deletion must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertDatabaseHas('customers', ['id' => $alpha['customer']->id, 'tenant_id' => $alpha['tenant']->id]);
        $this->assertDatabaseHas('customers', ['id' => $beta['customer']->id, 'tenant_id' => $beta['tenant']->id]);
    }

    public function test_foreign_parent_is_rejected_even_with_the_correct_tenant_id(): void
    {
        $alpha = $this->dataset('alpha');
        $beta = $this->dataset('beta');
        $this->actingAs($alpha['user']);
        try {
            TontineDay::query()->create(['tontine_id' => $beta['tontine']->id, 'due_date' => '2026-10-20', 'amount' => 50000]);
            $this->fail('Foreign parent must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertDatabaseMissing('tontine_days', ['tontine_id' => $beta['tontine']->id, 'due_date' => '2026-10-20']);
    }

    public function test_commands_can_work_with_a_tenant_and_restore_context_after_exception(): void
    {
        $data = $this->dataset('alpha');
        auth()->logout();
        $this->assertFalse(Context::hasHidden('tenant_id'));
        try {
            $data['tenant']->run(function () use ($data): void {
                $this->assertSame([$data['customer']->id], Customer::query()->pluck('id')->all());
                Customer::query()->create(['reference' => 'CLI-CLIENT', 'name' => 'Client importé']);
                throw new \RuntimeException('stop');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('stop', $exception->getMessage());
        }
        $this->assertFalse(Context::hasHidden('tenant_id'));
        $this->assertSame(0, Customer::query()->count());
        $this->assertDatabaseHas('customers', ['reference' => 'CLI-CLIENT', 'tenant_id' => $data['tenant']->id]);
    }

    public function test_tenant_member_cannot_switch_context_to_another_tenant(): void
    {
        $alpha = $this->dataset('alpha');
        $beta = $this->dataset('beta');
        $this->actingAs($alpha['user']);
        $this->expectException(HttpException::class);
        $beta['tenant']->run(fn () => Customer::query()->count());
    }

    public function test_creation_without_context_is_rejected(): void
    {
        auth()->logout();
        $this->expectException(HttpException::class);
        Customer::query()->create(['reference' => 'GUEST', 'name' => 'Interdit']);
    }

    public function test_suspended_tenant_is_hidden_even_outside_route_middleware(): void
    {
        $data = $this->dataset('alpha');
        $data['tenant']->update(['is_active' => false]);
        $this->assertSame(0, Customer::query()->count());
        auth()->logout();
        $this->expectException(HttpException::class);
        $data['tenant']->run(fn () => Customer::query()->count());
    }

    public function test_platform_admin_has_explicit_global_subscription_access_only(): void
    {
        $this->dataset('alpha');
        $this->dataset('beta');
        $admin = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true, 'phone' => '699000001']);
        $this->actingAs($admin);

        $this->assertSame(0, Subscription::query()->count());
        $this->assertSame(2, SubscriptionResource::getEloquentQuery()->count());
        $this->get('/workspace')->assertOk();
        $this->get('/workspace/subscriptions')->assertOk();
    }

    public function test_tenant_owner_cannot_use_global_administration_query(): void
    {
        $this->dataset('alpha');
        $this->expectException(AuthorizationException::class);
        SubscriptionResource::getEloquentQuery()->count();
    }

    public function test_platform_admin_can_create_subscription_for_selected_tenant(): void
    {
        $data = $this->dataset('alpha');
        $admin = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true, 'phone' => '699000001']);
        $this->actingAs($admin);
        $subscription = Subscription::query()->create(['tenant_id' => $data['tenant']->id, 'status' => 'active']);
        $this->assertSame($data['tenant']->id, $subscription->tenant_id);
        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'tenant_id' => $data['tenant']->id]);
    }

    public function test_existing_user_cannot_be_moved_to_a_different_tenant(): void
    {
        $alpha = $this->dataset('alpha');
        $beta = $this->dataset('beta');
        try {
            $alpha['user']->update(['tenant_id' => $beta['tenant']->id]);
            $this->fail('User tenant must be immutable.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertDatabaseHas('users', ['id' => $alpha['user']->id, 'tenant_id' => $alpha['tenant']->id]);
    }

    public function test_indirect_contribution_cannot_reference_another_tenants_customer(): void
    {
        $alpha = $this->dataset('alpha');
        $beta = $this->dataset('beta');
        $this->actingAs($alpha['user']);
        try {
            $alpha['contribution']->customer_id = $beta['customer']->id;
            $alpha['contribution']->save();
            $this->fail('Indirect tenant mismatch must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertDatabaseHas('contributions', ['id' => $alpha['contribution']->id, 'customer_id' => $alpha['customer']->id]);
    }

    public function test_payment_day_must_belong_to_the_same_tontine(): void
    {
        $data = $this->dataset('alpha');
        $otherTontine = Tontine::query()->create(['customer_id' => $data['customer']->id, 'reference' => 'TN-OTHER', 'daily_amount' => 50000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
        try {
            $data['payment']->update(['tontine_id' => $otherTontine->id]);
            $this->fail('A day of another tontine must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertDatabaseHas('tontine_payments', ['id' => $data['payment']->id, 'tontine_id' => $data['tontine']->id]);
    }

    public function test_plan_queries_are_shared_instead_of_scoped_by_tenant(): void
    {
        $this->assertStringNotContainsString('tenant_id', Plan::query()->toSql());
    }

    public function test_registration_still_creates_a_new_tenant_and_attaches_its_owner(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Nouveau propriétaire')->set('business_name', 'Nouvel espace')
            ->set('phone', '622000001')->set('email', 'owner@example.com')
            ->set('password', 'SecurePass1!')->set('password_confirmation', 'SecurePass1!')->set('terms', true)
            ->call('register')->assertHasNoErrors()->assertRedirect(route('login'));

        $user = User::query()->where('email', 'owner@example.com')->sole();
        $this->assertSame('Nouvel espace', $user->tenant->name);
        $this->assertSame('owner', $user->role);
        $this->assertFalse($user->isPlatformAdmin());
    }

    /** @return array{tenant: Tenant, user: User, customer: Customer, tontine: Tontine, day: TontineDay, payment: TontinePayment, subscription: Subscription, contribution: Contribution} */
    private function dataset(string $slug): array
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);
        $customer = Customer::query()->create(['name' => 'Client '.$slug, 'phone' => '620000001', 'address' => 'Conakry', 'status' => 'Actif']);
        $client = $customer;
        $tontine = Tontine::query()->create(['customer_id' => $client->id, 'reference' => 'TN-'.$slug, 'daily_amount' => 50000, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
        $day = TontineDay::query()->create(['tontine_id' => $tontine->id, 'due_date' => '2026-10-15', 'amount' => 50000]);
        $payment = TontinePayment::query()->create(['tontine_id' => $tontine->id, 'tontine_day_id' => $day->id, 'amount' => 50000, 'payment_date' => '2026-10-15', 'recorded_by' => $user->id]);
        $subscription = Subscription::query()->create(['status' => 'active']);
        $contribution = (new Contribution)->forceFill(['reference' => 'CO-'.$slug, 'tontine_id' => $tontine->id, 'customer_id' => $customer->id]);
        $contribution->save();

        return compact('tenant', 'user', 'customer', 'tontine', 'day', 'payment', 'subscription', 'contribution');
    }
}
