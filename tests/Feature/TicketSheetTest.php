<?php

namespace Tests\Feature;

use App\Livewire\TicketSheets;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TicketSheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_uses_tenant_settings_and_navigation_without_creating_business_records(): void
    {
        $user = $this->owner('alpha');
        Setting::current()->fill(['contact_phone' => '620000001', 'address' => 'Adresse alpha', 'ticket_footer' => 'Merci alpha'])->save();
        Customer::query()->create(['name' => 'Identité privée']);
        Livewire::actingAs($user)->test(TicketSheets::class)->assertSet('businessName', 'Espace alpha')
            ->assertSet('phone', '620000001')->assertSet('address', 'Adresse alpha')->assertSet('layout', '8')
            ->assertViewHas('design', fn ($design) => $design['totalCards'] === 8 && $design['columns'] === 2 && $design['rows'] === 4)
            ->assertSee('Nom du membre')->assertDontSee('Identité privée');
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Planches de tickets')->assertSee(route('tickets.sheets'), false);
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('tontine_payments', 0);
    }

    #[DataProvider('layouts')]
    public function test_print_generates_exact_number_of_blank_cards(string $layout, int $columns, int $rows): void
    {
        $user = $this->owner('alpha');
        $response = $this->actingAs($user)->get(route('tickets.sheets.print', ['layout' => $layout, 'pageCount' => 2]));
        $response->assertOk()->assertSee('size:A4 portrait', false)->assertSee('window.print()', false)
            ->assertSee('Nom du membre')->assertSee('Inscrit le :')->assertSee('Cases de cotisation de 1 à 31')
            ->assertViewHas('design', fn ($design) => $design['columns'] === $columns && $design['rows'] === $rows)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertSame(2 * (int) $layout, substr_count($response->getContent(), '<article class="blank-ticket"'));
        $this->assertSame(2, substr_count($response->getContent(), '<section class="ticket-sheet"'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('tontines', 0);
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_edits_update_preview_and_print_style_without_submitting(): void
    {
        $user = $this->owner('alpha');
        $editor = Livewire::actingAs($user)->test(TicketSheets::class)
            ->set('businessName', 'Mon nouveau comptoir')
            ->set('layout', '12')->set('pageCount', '2')
            ->set('numberCards', true)->set('startNumber', '42');

        foreach (['classic', 'orange', 'minimal'] as $style) {
            $editor->set('style', $style)->assertHasNoErrors()
                ->assertViewHas('design', fn ($design) => $design['style'] === $style && $design['businessName'] === 'Mon nouveau comptoir' && $design['totalCards'] === 24)
                ->assertSee('data-style="'.$style.'"', false);
            $this->get($editor->viewData('printUrl'))->assertOk()
                ->assertSee('data-style="'.$style.'"', false)->assertSee('000042')->assertSee('000065');
        }
    }

    public function test_invalid_live_edits_keep_last_valid_preview_until_corrected(): void
    {
        $editor = Livewire::actingAs($this->owner('alpha'))->test(TicketSheets::class)
            ->set('businessName', 'Modèle valide')->set('style', 'orange')
            ->set('layout', '99')->assertHasErrors(['layout'])
            ->assertViewHas('design', fn ($design) => $design['cardsPerPage'] === 8 && $design['businessName'] === 'Modèle valide')
            ->assertSee('Corrigez les champs pour imprimer.');
        $editor->set('layout', '6')->assertHasNoErrors()
            ->assertViewHas('design', fn ($design) => $design['cardsPerPage'] === 6);
        $this->getJson(route('tickets.sheets.print', ['style' => 'custom']))->assertUnprocessable()->assertJsonValidationErrors(['style']);
    }

    public function test_gallery_applies_templates_and_preserves_personalized_information(): void
    {
        $editor = Livewire::actingAs($this->owner('alpha'))->test(TicketSheets::class)
            ->assertSee('Commencez par un modèle')->assertSee('Choisir ce modèle')
            ->set('businessName', 'Ma tontine personnalisée')->set('phone', '620123456')
            ->set('footer', 'Merci de conserver votre carte.')->set('pageCount', '3');

        foreach (['minimal' => 12, 'orange' => 8, 'classic' => 8] as $template => $count) {
            $editor->call('chooseTemplate', $template)->assertHasNoErrors()
                ->assertSet('style', $template)->assertSet('layout', (string) $count)
                ->assertSet('color', ['minimal' => 'green', 'orange' => 'orange', 'classic' => 'blue'][$template])
                ->assertSet('businessName', 'Ma tontine personnalisée')->assertSet('phone', '620123456')
                ->assertSet('footer', 'Merci de conserver votre carte.')->assertSet('pageCount', '3')
                ->assertViewHas('design', fn ($design) => $design['cardsPerPage'] === $count && $design['style'] === $template);
        }
    }

    public function test_gallery_rejects_unknown_templates(): void
    {
        Livewire::actingAs($this->owner('alpha'))->test(TicketSheets::class)
            ->call('chooseTemplate', 'unknown')->assertStatus(422);
    }

    public function test_palette_updates_live_and_is_preserved_in_print_with_template_default_colors(): void
    {
        $editor = Livewire::actingAs($this->owner('alpha'))->test(TicketSheets::class);
        foreach (['orange', 'blue', 'green', 'purple', 'rose', 'graphite'] as $color) {
            $editor->set('color', $color)->assertHasNoErrors()
                ->assertViewHas('design', fn ($design) => $design['color'] === $color);
            $this->get($editor->viewData('printUrl'))->assertOk()->assertSee('data-color="'.$color.'"', false);
        }
        $editor->call('chooseTemplate', 'orange')->assertSet('color', 'orange');
        $editor->set('color', 'invalid')->assertHasErrors(['color'])
            ->assertViewHas('design', fn ($design) => $design['color'] === 'orange');
        $this->getJson(route('tickets.sheets.print', ['color' => '#ff0000;bad']))
            ->assertUnprocessable()->assertJsonValidationErrors(['color']);
    }

    public function test_monthly_card_aligns_real_weekdays_without_marking_payments(): void
    {
        $this->actingAs($this->owner('alpha'));
        $this->get(route('tickets.sheets.print', ['month' => '2026-10', 'layout' => '6']))
            ->assertOk()->assertSee('LUN')->assertSee('DIM')->assertSee('Nom du membre')
            ->assertSee('Total payé')->assertSee('Reste à payer')->assertSee('Statut')
            ->assertViewHas('design', fn ($design) => $design['calendarOffset'] === 3 && $design['calendarWeeks'] === 5 && $design['daysInMonth'] === 31);
        $this->get(route('tickets.sheets.print', ['month' => '2026-03']))->assertOk()
            ->assertViewHas('design', fn ($design) => $design['calendarOffset'] === 6 && $design['calendarWeeks'] === 6);
        $this->get(route('tickets.sheets.print', ['month' => '2027-02']))->assertOk()
            ->assertViewHas('design', fn ($design) => $design['calendarOffset'] === 0 && $design['calendarWeeks'] === 4 && $design['daysInMonth'] === 28);
        $this->get(route('tickets.sheets.print', ['month' => '']))->assertOk()
            ->assertViewHas('design', fn ($design) => $design['calendarOffset'] === 0 && $design['calendarWeeks'] === 5 && $design['monthLabel'] === null);
        $this->assertDatabaseCount('tontine_payments', 0);
    }

    public function test_logo_watermark_cannot_be_changed_outside_central_settings(): void
    {
        $editor = Livewire::actingAs($this->owner('alpha'))->test(TicketSheets::class)
            ->assertSet('watermark', true)->assertSet('watermarkSize', '22')
            ->assertSee('ouvrir les paramètres de votre espace')
            ->set('watermarkOpacity', '15')->set('watermarkSize', '25')->set('watermark', false)->assertHasNoErrors();
        $this->get($editor->viewData('printUrl'))->assertOk()
            ->assertSee('src="'.asset('ktontine-logo.png').'"', false)
            ->assertSee('--watermark-opacity:0.1;--watermark-size:22%', false);
        $this->getJson(route('tickets.sheets.print', ['watermarkOpacity' => '99', 'watermarkSize' => '-10']))
            ->assertUnprocessable()->assertJsonValidationErrors(['watermarkOpacity', 'watermarkSize']);
        $this->getJson(route('tickets.sheets.print', ['watermarkSize' => '40']))->assertUnprocessable()->assertJsonValidationErrors(['watermarkSize']);
    }

    public static function layouts(): array
    {
        return [['6', 2, 3], ['8', 2, 4], ['10', 2, 5], ['12', 3, 4]];
    }

    public function test_editor_can_configure_blank_month_amount_and_continuous_numbering(): void
    {
        $user = $this->owner('alpha');
        $page = Livewire::actingAs($user)->test(TicketSheets::class)->set('businessName', 'Mon modèle')
            ->set('month', '')->set('dailyAmount', '')->set('layout', '10')->set('pageCount', '2')
            ->set('numberCards', true)->set('startNumber', '51')->call('generate')->assertHasNoErrors()
            ->assertViewHas('design', fn ($design) => $design['totalCards'] === 20 && $design['monthLabel'] === null && $design['daysInMonth'] === 31);
        $this->get($page->viewData('printUrl'))->assertOk()->assertSee('000051')->assertSee('000070')->assertDontSee('000071');
    }

    public function test_validation_rejects_invalid_layout_dates_amount_and_page_count(): void
    {
        $user = $this->owner('alpha');
        Livewire::actingAs($user)->test(TicketSheets::class)->set('businessName', '')->set('layout', '100')
            ->set('pageCount', '21')->set('dailyAmount', '-1')->set('month', 'invalid')->call('generate')
            ->assertHasErrors(['businessName', 'layout', 'pageCount', 'dailyAmount', 'month']);
        $this->actingAs($user)->getJson(route('tickets.sheets.print', ['layout' => '99', 'pageCount' => '0', 'startNumber' => '-1']))
            ->assertUnprocessable()->assertJsonValidationErrors(['layout', 'pageCount', 'startNumber']);
    }

    public function test_print_escapes_model_content_and_handles_leap_month(): void
    {
        $user = $this->owner('alpha');
        $this->actingAs($user)->get(route('tickets.sheets.print', ['businessName' => '<script>alert(1)</script>', 'footer' => '<b>Bonjour</b>', 'month' => '2028-02']))
            ->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;b&gt;Bonjour&lt;/b&gt;', false)->assertViewHas('design', fn ($design) => $design['daysInMonth'] === 29);
    }

    public function test_default_model_never_loads_other_tenants_settings(): void
    {
        $alpha = $this->owner('alpha');
        Setting::current()->fill(['address' => 'Adresse confidentielle alpha'])->save();
        $beta = $this->owner('beta');
        $this->actingAs($beta)->get(route('tickets.sheets.print', ['tenant_id' => $alpha->tenant_id]))->assertOk()
            ->assertSee('Espace beta')->assertDontSee('Espace alpha')->assertDontSee('Adresse confidentielle alpha');
    }

    public function test_guests_and_suspended_tenants_cannot_generate_or_print(): void
    {
        foreach (['tickets.sheets', 'tickets.sheets.print'] as $route) {
            $this->get(route($route))->assertRedirectToRoute('login');
        }
        $user = $this->owner('alpha');
        $user->tenant()->update(['is_active' => false]);
        $this->actingAs($user)->get(route('tickets.sheets'))->assertForbidden();
        $this->actingAs($user)->get(route('tickets.sheets.print'))->assertForbidden();
        Livewire::actingAs($user)->test(TicketSheets::class)->assertForbidden();
    }

    private function owner(string $slug): User
    {
        $tenant = Tenant::query()->create(['name' => 'Espace '.$slug, 'slug' => $slug, 'is_active' => true]);
        $user = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }
}
