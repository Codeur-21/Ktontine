<?php

namespace Tests\Feature;

use App\Filament\Pages\PlatformSettings;
use App\Livewire\Parametre;
use App\Livewire\TicketSheets;
use App\Models\PlatformSetting;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\TicketSheet;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WorkspaceSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_has_one_central_page_without_a_tenant_selector(): void
    {
        $this->admin();
        $this->get(PlatformSettings::getUrl())->assertOk()->assertSee('Paramètres centraux')->assertSee('Aucun tontinier n’est à sélectionner.')->assertDontSee('Configurer un tontinier');
        Livewire::test(PlatformSettings::class)->assertFormSet(['name' => 'Ktontine', 'ticket_preferences.watermarkSize' => 22]);
        $this->assertDatabaseCount('platform_settings', 0);
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_admin_saves_one_configuration_and_cancel_or_defaults_do_not_persist(): void
    {
        $this->admin();
        $editor = Livewire::test(PlatformSettings::class)->fillForm([
            'name' => 'Ktontine Central', 'contact_email' => 'support@example.com', 'ticket_paper' => 'A5', 'priority_days' => 3,
            'ticket_preferences' => [...TicketSheet::PREFERENCES, 'style' => 'minimal', 'color' => 'green', 'watermarkOpacity' => 15, 'watermarkSize' => 25],
        ])->call('save')->assertHasNoFormErrors();
        $this->assertDatabaseHas('platform_settings', ['id' => 1, 'name' => 'Ktontine Central', 'contact_email' => 'support@example.com', 'ticket_paper' => 'A5']);
        $editor->fillForm(['name' => 'Ktontine Central 2'])->call('save')->assertHasNoFormErrors()
            ->call('defaultPreferences')->assertFormSet(['ticket_preferences.watermarkSize' => 22, 'ticket_paper' => 'A4']);
        $this->assertSame(25, PlatformSetting::current()->ticket_preferences['watermarkSize']);
        $editor->call('cancelChanges')->assertFormSet(['ticket_preferences.watermarkSize' => 25, 'ticket_paper' => 'A5']);
        $this->assertDatabaseCount('platform_settings', 1);
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_central_preferences_apply_to_all_tenants_without_replacing_their_identity(): void
    {
        $this->admin();
        Livewire::test(PlatformSettings::class)->fillForm([
            'ticket_paper' => 'A5', 'priority_days' => 3,
            'ticket_preferences' => [...TicketSheet::PREFERENCES, 'style' => 'minimal', 'color' => 'green', 'layout' => '6', 'watermarkOpacity' => 15, 'watermarkSize' => 25],
        ])->call('save')->assertHasNoFormErrors();
        foreach (['alpha', 'beta'] as $slug) {
            $owner = $this->owner($slug);
            Setting::current()->fill(['address' => 'Adresse '.$slug, 'ticket_preferences' => [...TicketSheet::PREFERENCES, 'color' => 'rose', 'watermarkSize' => 18]])->save();
            Livewire::actingAs($owner)->test(TicketSheets::class)->assertSet('businessName', $slug)
                ->assertSet('address', 'Adresse '.$slug)->assertSet('style', 'minimal')->assertSet('color', 'green')
                ->assertSet('layout', '6')->assertSet('watermarkOpacity', '15')->assertSet('watermarkSize', '25');
            Livewire::actingAs($owner)->test(Parametre::class)->assertViewHas('ticketPreferencesManagedCentrally', true)
                ->set('ticketPreferences.watermark', false)->set('ticketPreferences.watermarkSize', '15')->call('save')->assertHasNoErrors();
            $this->assertTrue(PlatformSetting::current()->ticket_preferences['watermark']);
            $this->assertSame(25, PlatformSetting::current()->ticket_preferences['watermarkSize']);
        }
        $this->assertDatabaseCount('platform_settings', 1);
        $this->assertDatabaseCount('settings', 2);
    }

    public function test_print_cannot_override_centrally_managed_watermark(): void
    {
        $admin = $this->admin();
        Livewire::test(PlatformSettings::class)->fillForm([
            'ticket_preferences' => [...TicketSheet::PREFERENCES, 'watermarkOpacity' => 15, 'watermarkSize' => 25],
        ])->call('save')->assertHasNoFormErrors();
        $owner = $this->owner('alpha');
        $this->get(route('tickets.sheets.print', ['watermark' => false, 'watermarkSize' => 15, 'watermarkOpacity' => 3]))->assertOk()
            ->assertSee('src="'.asset('ktontine-logo.png').'"', false)->assertSee('--watermark-opacity:0.15;--watermark-size:25%', false);
        Livewire::actingAs($admin)->test(PlatformSettings::class)->fillForm(['ticket_preferences.watermark' => false])->call('save')->assertHasNoFormErrors();
        $this->actingAs($owner)->get(route('tickets.sheets.print', ['watermark' => true]))->assertOk()->assertDontSee('src="'.asset('ktontine-logo.png').'"', false);
    }

    public function test_invalid_global_preferences_do_not_save(): void
    {
        $this->admin();
        Livewire::test(PlatformSettings::class)->fillForm([
            'name' => '', 'contact_email' => 'invalid', 'priority_days' => 0,
            'ticket_preferences.watermarkOpacity' => 99, 'ticket_preferences.watermarkSize' => 70,
        ])->call('save')->assertHasFormErrors(['name', 'contact_email', 'priority_days', 'ticket_preferences.watermarkOpacity', 'ticket_preferences.watermarkSize']);
        $this->assertDatabaseCount('platform_settings', 0);
    }

    public function test_only_active_platform_admins_can_open_or_save_central_settings(): void
    {
        $admin = $this->admin();
        $editor = Livewire::test(PlatformSettings::class);
        $admin->update(['is_active' => false]);
        $editor->call('save')->assertForbidden();
        foreach (['owner', 'manager', 'agent'] as $role) {
            $user = $this->owner($role);
            $user->update(['role' => $role]);
            Livewire::actingAs($user)->test(PlatformSettings::class)->assertForbidden();
        }
        $this->assertDatabaseCount('platform_settings', 0);
    }

    public function test_admin_uploads_a_logo_used_by_the_panel_front_and_tickets(): void
    {
        Storage::fake('public');
        $this->admin();
        Livewire::test(PlatformSettings::class)->fillForm([
            'logo_path' => UploadedFile::fake()->image('logo.png', 160, 160),
        ])->call('save')->assertHasNoFormErrors();
        $settings = PlatformSetting::current();
        Storage::disk('public')->assertExists($settings->logo_path);
        $url = Storage::disk('public')->url($settings->logo_path);
        $this->assertSame($url, Filament::getPanel('workspace')->getBrandLogo());
        $this->assertSame($url, Filament::getPanel('workspace')->getFavicon());
        $this->get(PlatformSettings::getUrl())->assertOk()->assertSee($url, false)
            ->assertSee('id="workspace-footer"', false)->assertSee('Administration de la plateforme')->assertSee('Tous droits réservés.');
        $this->get('/')->assertOk()->assertSee('<link rel="icon" href="'.$url.'">', false);
        $this->get(route('login'))->assertOk()->assertSee('<link rel="icon" href="'.$url.'">', false);
        $owner = $this->owner('logo-owner');
        $this->actingAs($owner)->get(route('tickets.sheets.print'))->assertOk()->assertSee($url, false);
    }

    public function test_removing_the_custom_logo_restores_the_default_image(): void
    {
        Storage::fake('public');
        $this->admin();
        Storage::disk('public')->put('platform/logos/old.png', 'old-image');
        PlatformSetting::current()->fill(['logo_path' => 'platform/logos/old.png'])->save();
        Livewire::test(PlatformSettings::class)->fillForm(['logo_path' => null])->call('save')->assertHasNoFormErrors();
        $this->assertNull(PlatformSetting::current()->logo_path);
        $this->assertSame(asset('ktontine-logo.png'), Filament::getPanel('workspace')->getBrandLogo());
    }

    public function test_logo_field_rejects_an_unowned_existing_storage_path(): void
    {
        Storage::fake('public');
        $this->admin();
        Storage::disk('public')->put('other-file.png', 'not-a-platform-logo');
        Livewire::test(PlatformSettings::class)->fillForm(['logo_path' => ['tampered' => 'other-file.png']])
            ->call('save')->assertHasFormErrors(['logo_path']);
        $this->assertDatabaseCount('platform_settings', 0);
    }

    private function admin(): User
    {
        $user = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('workspace'));

        return $user;
    }

    private function owner(string $slug): User
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }
}
