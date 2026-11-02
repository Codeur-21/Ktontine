<?php

namespace Tests\Feature;

use App\Filament\Pages\Communication;
use App\Jobs\SendPlatformMail;
use App\Mail\TenantCommunication;
use App\Models\EmailDelivery;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class TenantCommunicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
        Filament::setCurrentPanel(Filament::getPanel('workspace'));
    }

    public function test_marketing_preview_does_not_send_and_confirmation_sends_separate_personalized_mails(): void
    {
        $first = $this->owner('alpha');
        $second = $this->owner('beta');
        $this->admin();
        $this->get(Communication::getUrl())->assertOk()->assertSee('Communication')->assertSee('Message individuel');
        $page = Livewire::test(Communication::class)->fillForm([
            'mode' => 'marketing', 'recipientIds' => [$first->id, $second->id],
            'subject' => 'Bonjour {nom}', 'messageBody' => 'Des nouvelles pour {espace}, {nom}.',
        ])->call('prepare')->assertHasNoFormErrors()->assertDispatched('communication-preview');
        Mail::assertNothingSent();
        $this->assertCount(2, $page->get('prepared')['recipients']);
        $page->call('sendPrepared')->assertSet('prepared', null)->assertDispatched('communication-finished');
        Queue::assertPushed(SendPlatformMail::class, 2);
        Mail::assertNothingSent();
        $this->assertSame(['queued', 'queued'], array_column($page->get('results'), 'status'));
        foreach (EmailDelivery::query()->get() as $delivery) {
            (new SendPlatformMail($delivery->id))->handle();
        }
        $page->call('refreshResults');
        Mail::assertSent(TenantCommunication::class, fn (TenantCommunication $mail): bool => $mail->hasTo($first->email) && count($mail->to) === 1 && $mail->businessName === 'alpha');
        $this->assertSame(['accepted', 'accepted'], array_column($page->get('results'), 'status'));
        $page->call('sendPrepared')->assertStatus(422);
        Mail::assertSent(TenantCommunication::class, 2);
    }

    public function test_individual_message_can_reach_an_inactive_owner_and_shortcut_preselects_recipient(): void
    {
        $owner = $this->owner('alpha', false);
        $this->admin();
        $this->get(Communication::getUrl(['recipient' => $owner->id]))->assertOk()->assertSee('alpha');
        Livewire::test(Communication::class)->fillForm([
            'mode' => 'individual', 'recipientId' => $owner->id, 'subject' => 'Votre espace', 'messageBody' => 'Bonjour {nom}',
        ])->call('prepare')->assertHasNoFormErrors()->call('sendPrepared');
        Queue::assertPushed(SendPlatformMail::class, 1);
        (new SendPlatformMail(EmailDelivery::query()->sole()->id))->handle();
        Mail::assertSent(TenantCommunication::class, fn (TenantCommunication $mail): bool => $mail->hasTo($owner->email));
    }

    public function test_nonowners_and_inactive_owners_cannot_be_targeted_by_marketing(): void
    {
        $inactive = $this->owner('alpha', false);
        $agent = UserFactory::new()->create(['tenant_id' => $inactive->tenant_id, 'role' => 'agent']);
        $this->admin();
        foreach ([$inactive, $agent] as $recipient) {
            Livewire::test(Communication::class)->fillForm([
                'mode' => 'marketing', 'recipientIds' => [$recipient->id], 'subject' => 'Test', 'messageBody' => 'Bonjour',
            ])->call('prepare')->assertHasFormErrors();
        }
        Mail::assertNothingSent();
    }

    public function test_invalid_subject_and_unsafe_button_url_cannot_be_prepared(): void
    {
        $owner = $this->owner('alpha');
        $this->admin();
        Livewire::test(Communication::class)->fillForm([
            'mode' => 'individual', 'recipientId' => $owner->id, 'subject' => "Sujet\r\nBcc: other@example.com", 'messageBody' => 'Bonjour',
            'buttonLabel' => 'Ouvrir', 'buttonUrl' => 'javascript:alert(1)',
        ])->call('prepare')->assertHasFormErrors(['subject', 'buttonUrl']);
        Mail::assertNothingSent();
    }

    public function test_recipient_changes_after_preview_require_a_new_preview(): void
    {
        $owner = $this->owner('alpha');
        $this->admin();
        $page = Livewire::test(Communication::class)->fillForm([
            'mode' => 'individual', 'recipientId' => $owner->id, 'subject' => 'Test', 'messageBody' => 'Bonjour',
        ])->call('prepare')->assertHasNoFormErrors();
        $owner->update(['email' => 'changed@example.com']);
        $page->call('sendPrepared')->assertSet('prepared', null);
        Mail::assertNothingSent();
    }

    public function test_processed_batch_is_not_sent_twice(): void
    {
        $owner = $this->owner('alpha');
        $this->admin();
        $page = Livewire::test(Communication::class)->fillForm([
            'mode' => 'individual', 'recipientId' => $owner->id, 'subject' => 'Test', 'messageBody' => 'Bonjour',
        ])->call('prepare');
        Cache::put('communication.batch.'.$page->get('prepared')['token'], true, 60);
        $page->call('sendPrepared');
        Mail::assertNothingSent();
    }

    public function test_tenant_users_cannot_open_communication_and_suspended_admin_cannot_send(): void
    {
        $owner = $this->owner('alpha');
        $this->actingAs($owner)->get(Communication::getUrl())->assertForbidden();
        $admin = $this->admin();
        $page = Livewire::test(Communication::class)->fillForm([
            'mode' => 'individual', 'recipientId' => $owner->id, 'subject' => 'Test', 'messageBody' => 'Bonjour',
        ])->call('prepare');
        $admin->update(['is_active' => false]);
        $page->call('sendPrepared')->assertForbidden();
        Mail::assertNothingSent();
    }

    public function test_mail_failure_is_reported_without_claiming_success(): void
    {
        $owner = $this->owner('alpha');
        $this->admin();
        $page = Livewire::test(Communication::class)->fillForm([
            'mode' => 'individual', 'recipientId' => $owner->id, 'subject' => 'Test', 'messageBody' => 'Bonjour',
        ])->call('prepare')->call('sendPrepared')->assertSet('prepared', null);
        $delivery = EmailDelivery::query()->sole();
        (new SendPlatformMail($delivery->id))->failed(new \RuntimeException('SMTP indisponible'));
        $page->call('refreshResults')
            ->assertSet('results', [['name' => $owner->name, 'email' => $owner->email, 'status' => 'failed']]);
        Mail::assertNothingSent();
    }

    public function test_templates_only_prepare_a_draft_and_central_users_are_not_recipients(): void
    {
        $admin = $this->admin();
        Livewire::test(Communication::class)->call('useTemplate', 'news')
            ->assertSet('data.subject', 'Une nouveauté vous attend sur Ktontine')
            ->fillForm(['mode' => 'individual', 'recipientId' => $admin->id, 'messageBody' => 'Bonjour'])
            ->call('prepare')->assertHasFormErrors();
        Mail::assertNothingSent();
    }

    public function test_template_modal_requires_confirmation_and_cancellation_keeps_the_draft(): void
    {
        $this->admin();
        $page = Livewire::test(Communication::class)->fillForm(['subject' => 'Mon objet', 'messageBody' => 'Mon brouillon'])
            ->mountAction('newsTemplate')->assertActionMounted('newsTemplate')
            ->assertSet('data.subject', 'Mon objet')->assertSet('data.messageBody', 'Mon brouillon');
        $page->unmountAction()->assertSet('data.messageBody', 'Mon brouillon');
        $page->mountAction('offerTemplate')->callMountedAction()
            ->assertSet('data.subject', 'Une offre pour votre activité');
        $page->mountAction('personalTemplate')->callMountedAction()
            ->assertSet('data.subject', 'Un message concernant votre espace Ktontine');
        Mail::assertNothingSent();
    }

    public function test_mail_escapes_content_and_personalizes_subject_html_and_text(): void
    {
        $mail = new TenantCommunication('Mariam', 'Espace alpha', 'Bonjour {nom}', 'Bonjour {nom}, votre espace {espace}. <script>alert(1)</script>');
        $mail->assertHasSubject('Bonjour Mariam');
        $mail->assertSeeInHtml('Bonjour Mariam, votre espace Espace alpha.');
        $mail->assertDontSeeInHtml('<script>alert(1)</script>', escape: false);
        $mail->assertSeeInText('Bonjour Mariam, votre espace Espace alpha.');
    }

    private function admin(): User
    {
        $admin = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($admin);

        return $admin;
    }

    private function owner(string $slug, bool $active = true): User
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => $active]);

        return UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => $active]);
    }
}
