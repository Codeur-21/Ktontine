<?php

namespace Tests\Feature;

use App\Filament\Pages\PlatformSettings;
use App\Models\PlatformSetting;
use App\Models\Setting;
use App\Models\Tenant;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupportContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contacts_configured_in_filament_are_shared_by_the_public_site_and_tenant_dashboard(): void
    {
        $admin = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('workspace'));
        Livewire::test(PlatformSettings::class)->fillForm([
            'contact_phone' => '+224 620 00 00 00', 'contact_email' => 'support@example.com', 'address' => 'Conakry',
        ])->call('save')->assertHasNoFormErrors();
        $this->get('/')->assertOk()->assertSee('Nous contacter')->assertSee('href="https://wa.me/224620000000"', false)
            ->assertSee('href="mailto:support@example.com"', false)->assertSee('Conakry');
        $tenant = Tenant::query()->create(['name' => 'alpha', 'slug' => 'alpha', 'is_active' => true]);
        $owner = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($owner);
        Setting::current()->fill(['contact_email' => 'tenant-private@example.com'])->save();
        $this->get(route('dashboard'))->assertOk()->assertSee('Contacter le support')->assertSee('support@example.com')
            ->assertDontSee('tenant-private@example.com');
    }

    public function test_missing_contact_details_display_an_empty_state_without_fake_links(): void
    {
        $this->get('/')->assertOk()->assertSee('Les coordonnées arrivent bientôt.')
            ->assertDontSee('Discuter sur WhatsApp')->assertDontSee('Envoyer un e-mail');
        $this->assertDatabaseCount('platform_settings', 0);
    }

    public function test_local_phone_number_opens_whatsapp_with_the_guinean_country_code(): void
    {
        PlatformSetting::current()->fill(['contact_phone' => '626 42 74 42'])->save();
        $this->get('/')->assertOk()->assertSee('href="https://wa.me/224626427442"', false)
            ->assertSee('Discuter sur WhatsApp')->assertDontSee('626 42 74 42');
    }

    public function test_configured_address_is_escaped_in_the_contact_dialog(): void
    {
        PlatformSetting::current()->fill(['address' => '<script>alert(1)</script>'])->save();
        $this->get('/')->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
