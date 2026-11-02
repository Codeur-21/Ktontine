<?php

namespace Tests\Feature;

use App\Livewire\Parametre;
use App\Livewire\Rapport;
use App\Livewire\Retard;
use App\Livewire\TicketSheets;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\Tontine;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ParametreTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_visit_uses_defaults_without_creating_a_settings_row(): void
    {
        $owner = $this->user('alpha');
        Livewire::actingAs($owner)->test(Parametre::class)->assertSet('businessName', 'alpha')
            ->assertSet('ticketPaper', 'A4')->assertSet('showSignatures', true)->assertSet('priorityDays', '7');
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_owner_saves_shared_settings_only_for_their_tenant(): void
    {
        $owner = $this->user('alpha');
        $page = Livewire::actingAs($owner)->test(Parametre::class)->set('businessName', 'Mon espace')
            ->set('contactPhone', '+224 620 00 00 00')->set('contactEmail', 'contact@example.com')->set('address', 'Conakry')
            ->set('ticketPaper', 'A5')->set('ticketFooter', 'Merci de votre confiance')->set('showCommission', false)
            ->set('priorityDays', '3')->set('reportPeriod', 'today')->call('save')->assertHasNoErrors()->assertSee('Les paramètres de votre espace ont été enregistrés.');
        $page->set('ticketFooter', 'Message mis à jour')->call('save')->assertHasNoErrors();
        $this->assertDatabaseCount('settings', 1);
        $this->assertDatabaseHas('settings', ['tenant_id' => $owner->tenant_id, 'ticket_paper' => 'A5', 'priority_days' => 3, 'contact_email' => 'contact@example.com', 'show_commission' => false]);
        $this->assertDatabaseHas('tenants', ['id' => $owner->tenant_id, 'name' => 'Mon espace', 'slug' => 'alpha']);
        Livewire::actingAs($owner)->test(Parametre::class)->assertSet('ticketFooter', 'Message mis à jour')->assertSet('reportPeriod', 'today');
    }

    public function test_validation_prevents_partial_updates(): void
    {
        $owner = $this->user('alpha');
        Livewire::actingAs($owner)->test(Parametre::class)->set('businessName', '  ')->set('contactEmail', 'invalide')
            ->set('contactPhone', 'abcdefg')->set('ticketPaper', 'A0')->set('priorityDays', '0')->set('reportPeriod', 'year')
            ->call('save')->assertHasErrors(['businessName', 'contactEmail', 'contactPhone', 'ticketPaper', 'priorityDays', 'reportPeriod']);
        $this->assertDatabaseCount('settings', 0);
        $this->assertDatabaseHas('tenants', ['id' => $owner->tenant_id, 'name' => 'alpha']);
    }

    public function test_other_tenant_settings_are_not_visible_or_changed(): void
    {
        $alpha = $this->user('alpha');
        Livewire::actingAs($alpha)->test(Parametre::class)->set('contactEmail', 'alpha@example.com')->call('save');
        $beta = $this->user('beta');
        Livewire::actingAs($beta)->test(Parametre::class)->assertSet('contactEmail', '')
            ->set('contactEmail', 'beta@example.com')->call('save')->assertHasNoErrors();
        Livewire::actingAs($alpha)->test(Parametre::class)->assertSet('contactEmail', 'alpha@example.com')->assertDontSee('beta@example.com');
        $this->assertDatabaseHas('settings', ['tenant_id' => $alpha->tenant_id, 'contact_email' => 'alpha@example.com']);
        $this->assertDatabaseHas('settings', ['tenant_id' => $beta->tenant_id, 'contact_email' => 'beta@example.com']);
    }

    public function test_agents_and_managers_cannot_change_shared_settings(): void
    {
        foreach (['agent', 'manager'] as $role) {
            $member = $this->user($role, $role);
            Livewire::actingAs($member)->test(Parametre::class)->assertSee('Lecture seule')
                ->set('businessName', 'Interdit')->call('save')->assertForbidden();
            Livewire::actingAs($member)->test(Parametre::class)->call('defaultPreferences')->assertForbidden();
        }
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_cancel_and_defaults_do_not_save_without_explicit_submit(): void
    {
        $owner = $this->user('alpha');
        $page = Livewire::actingAs($owner)->test(Parametre::class)->set('ticketPaper', 'A5')->set('priorityDays', '3')->call('save');
        $page->call('defaultPreferences')->assertSet('ticketPaper', 'A4')->assertSet('priorityDays', '7');
        $this->assertDatabaseHas('settings', ['tenant_id' => $owner->tenant_id, 'ticket_paper' => 'A5', 'priority_days' => 3]);
        $page->call('cancelChanges')->assertSet('ticketPaper', 'A5')->assertSet('priorityDays', '3');
    }

    public function test_ticket_and_report_print_use_contact_and_ticket_preferences(): void
    {
        $owner = $this->user('alpha');
        Livewire::actingAs($owner)->test(Parametre::class)->set('contactEmail', 'print@example.com')->set('address', 'Adresse officielle')
            ->set('ticketPaper', 'A5')->set('showCommission', false)->set('showSignatures', false)
            ->set('ticketFooter', '<script>alert(1)</script>')->call('save')->assertHasNoErrors();
        $customer = Customer::query()->create(['name' => 'Cliente', 'phone' => '620000001', 'address' => 'Conakry', 'status' => 'Actif']);
        $client = $customer;
        Tontine::query()->create(['customer_id' => $client->id, 'reference' => 'TN-PRINT', 'daily_amount' => 50000, 'start_date' => '2026-10-20', 'end_date' => '2026-10-26']);
        $this->actingAs($owner)->get(route('clients.ticket', ['customer' => $customer->id]))->assertOk()->assertSee('size:A5', false)
            ->assertSee('print@example.com')->assertSee('Adresse officielle')->assertDontSee('Commission prévue, séparée')->assertDontSee('Signature du client')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('rapports.export', ['startDate' => '2026-10-01', 'endDate' => '2026-10-24']))->assertOk()->assertSee('print@example.com')->assertSee('Adresse officielle');
    }

    public function test_default_report_period_and_arrears_priority_are_applied(): void
    {
        $owner = $this->user('alpha');
        Livewire::actingAs($owner)->test(Parametre::class)->set('reportPeriod', 'today')->set('priorityDays', '2')->call('save')->assertHasNoErrors();
        Livewire::actingAs($owner)->test(Rapport::class)->assertSet('reportStart', '2026-10-24')->assertSet('reportEnd', '2026-10-24');
        Setting::current()->update(['report_period' => 'week']);
        Livewire::actingAs($owner)->test(Rapport::class)->assertSet('reportStart', '2026-10-19');
        $client = Customer::query()->create(['reference' => 'CL-TEST', 'name' => 'Cliente']);
        Tontine::query()->create(['customer_id' => $client->id, 'reference' => 'TN-PRIORITY', 'daily_amount' => 50000, 'start_date' => '2026-10-20', 'end_date' => '2026-10-26']);
        Livewire::actingAs($owner)->test(Retard::class)->set('minimumDays', 'priority')
            ->assertViewHas('priorityDays', 2)->assertViewHas('rows', fn ($rows) => $rows->total() === 1)
            ->assertViewHas('totals', fn ($totals) => $totals['amount'] === 200000);
    }

    public function test_password_change_hashes_password_rotates_token_and_revokes_only_other_own_sessions(): void
    {
        $owner = $this->user('alpha', 'agent');
        $other = $this->user('beta');
        $this->actingAs($owner);
        DB::table('sessions')->insert([
            ['id' => 'old-own-session', 'user_id' => $owner->id, 'payload' => '', 'last_activity' => now()->timestamp],
            ['id' => 'foreign-session', 'user_id' => $other->id, 'payload' => '', 'last_activity' => now()->timestamp],
        ]);
        $token = $owner->remember_token;
        Livewire::actingAs($owner)->test(Parametre::class)->set('currentPassword', 'SecurePass1!')
            ->set('password', 'NewSecurePass2!')->set('password_confirmation', 'NewSecurePass2!')->call('changePassword')
            ->assertHasNoErrors()->assertSet('password', '')->assertSet('currentPassword', '')->assertSee('Votre mot de passe a été modifié');
        $fresh = $owner->fresh();
        $this->assertTrue(Hash::check('NewSecurePass2!', $fresh->password));
        $this->assertNotSame($token, $fresh->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'old-own-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'foreign-session']);
        $this->assertAuthenticatedAs($owner);
    }

    public function test_incorrect_password_or_confirmation_does_not_change_credentials(): void
    {
        $owner = $this->user('alpha');
        $hash = $owner->password;
        Livewire::actingAs($owner)->test(Parametre::class)->set('currentPassword', 'incorrect')
            ->set('password', 'NewSecurePass2!')->set('password_confirmation', 'NewSecurePass2!')->call('changePassword')->assertHasErrors('currentPassword');
        Livewire::actingAs($owner)->test(Parametre::class)->set('currentPassword', 'SecurePass1!')
            ->set('password', 'NewSecurePass2!')->set('password_confirmation', 'different')->call('changePassword')->assertHasErrors('password');
        $this->assertSame($hash, $owner->fresh()->password);
    }

    public function test_repeated_incorrect_current_password_is_throttled(): void
    {
        $owner = $this->user('alpha');
        $page = Livewire::actingAs($owner)->test(Parametre::class);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $page->set('currentPassword', 'incorrect')->set('password', 'NewSecurePass2!')
                ->set('password_confirmation', 'NewSecurePass2!')->call('changePassword')->assertHasErrors('currentPassword');
        }
        $page->set('currentPassword', 'SecurePass1!')->set('password', 'NewSecurePass2!')
            ->set('password_confirmation', 'NewSecurePass2!')->call('changePassword')
            ->assertHasErrors('currentPassword')->assertSee('Trop de tentatives.')->assertSet('password', '');
        $this->assertTrue(Hash::check('SecurePass1!', $owner->fresh()->password));
    }

    public function test_guest_and_suspended_tenant_cannot_access_settings(): void
    {
        $this->get(route('parametres'))->assertRedirectToRoute('login');
        $owner = $this->user('alpha');
        $owner->tenant()->update(['is_active' => false]);
        Livewire::actingAs($owner)->test(Parametre::class)->assertForbidden();
        $this->actingAs($owner)->get(route('parametres'))->assertForbidden();
    }

    public function test_ticket_preferences_are_central_and_tenant_changes_do_not_override_them(): void
    {
        $owner = $this->user('alpha');
        $settings = Livewire::actingAs($owner)->test(Parametre::class)
            ->assertViewHas('ticketPreferencesManagedCentrally', true)->assertSet('ticketPreferences.watermarkSize', '22')
            ->set('ticketPreferences.style', 'minimal')->set('ticketPreferences.color', 'green')
            ->set('ticketPreferences.layout', '6')->set('ticketPreferences.watermark', false)
            ->set('ticketPreferences.watermarkSize', '25')->call('save')->assertHasNoErrors();
        Livewire::actingAs($owner)->test(TicketSheets::class)->assertSet('style', 'classic')->assertSet('color', 'blue')
            ->assertSet('layout', '8')->assertSet('watermark', true)->assertSet('watermarkSize', '22');
        $this->actingAs($owner)->get(route('tickets.sheets.print', ['watermark' => false, 'watermarkSize' => 25]))->assertOk()
            ->assertSee('src="'.asset('ktontine-logo.png').'"', false)->assertSee('--watermark-size:22%', false);
        $settings->call('defaultPreferences')->assertSet('ticketPreferences.watermark', true)
            ->call('cancelChanges')->assertSet('ticketPreferences.watermarkSize', '22');
        $beta = $this->user('beta');
        Livewire::actingAs($beta)->test(TicketSheets::class)->assertSet('style', 'classic')->assertSet('watermarkSize', '22');
        $this->assertDatabaseCount('settings', 1);
        $this->assertDatabaseCount('platform_settings', 0);
    }

    public function test_invalid_tenant_ticket_overrides_are_ignored_but_shared_settings_still_validate(): void
    {
        $owner = $this->user('alpha');
        Livewire::actingAs($owner)->test(Parametre::class)->set('contactEmail', 'invalid')
            ->set('ticketPreferences.style', 'invalid')->set('ticketPreferences.watermarkSize', '70')->call('save')
            ->assertHasErrors(['contactEmail'])->assertSet('ticketPreferences.watermarkSize', '22');
        $this->assertDatabaseCount('settings', 0);
        $this->assertDatabaseCount('platform_settings', 0);
        $agent = $this->user('agent', 'agent');
        Livewire::actingAs($agent)->test(Parametre::class)->call('save')->assertForbidden();
    }

    private function user(string $slug, string $role = 'owner'): User
    {
        $this->travelTo(Carbon::parse('2026-10-24 10:00:00'));
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'phone' => str_pad((string) $tenant->id, 9, '0', STR_PAD_LEFT), 'role' => $role, 'is_active' => true, 'password' => 'SecurePass1!']);
        $this->actingAs($user);

        return $user;
    }
}
