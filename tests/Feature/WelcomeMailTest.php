<?php

namespace Tests\Feature;

use App\Jobs\SendPlatformMail;
use App\Livewire\Auth\Register;
use App\Mail\Welcome;
use App\Models\EmailDelivery;
use App\Models\User;
use App\PlatformMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class WelcomeMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_queues_welcome_to_the_new_owner_with_their_own_space(): void
    {
        Mail::fake();
        Queue::fake();
        $this->registration()->call('register')->assertHasNoErrors()->assertRedirect(route('login'));
        $owner = User::query()->where('email', 'owner@example.com')->sole();
        $this->assertDatabaseHas('tenants', ['id' => $owner->tenant_id, 'name' => 'Mon espace']);
        $this->assertDatabaseCount('users', 1);
        Queue::assertPushedOn('emails', SendPlatformMail::class);
        Mail::assertNothingSent();
        $delivery = EmailDelivery::query()->sole();
        $this->assertSame($owner->id, $delivery->recipient_id);
        $this->assertSame('Mon espace', $delivery->business_name);
        $this->assertSame(route('login'), $delivery->payload['loginUrl']);

    }

    public function test_welcome_has_html_and_text_content_and_escapes_user_input(): void
    {
        $mail = new Welcome('<b>Abdoulaye</b>', 'Mon espace', route('login'));
        $mail->assertHasSubject('Bienvenue sur Ktontine !');
        $mail->assertSeeInHtml('&lt;b&gt;Abdoulaye&lt;/b&gt;', false);
        $mail->assertDontSeeInHtml('<b>Abdoulaye</b>', false);
        $mail->assertSeeInHtml('Mon espace')->assertSeeInHtml(route('login'));
        $mail->assertSeeInText('Mon espace')->assertSeeInText(route('login'));
    }

    public function test_invalid_registration_does_not_send_mail_or_create_an_account(): void
    {
        Mail::fake();
        $this->registration()->set('email', 'invalid')->call('register')->assertHasErrors('email');
        Mail::assertNothingSent();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('tenants', 0);
    }

    public function test_queue_dispatch_failure_preserves_created_account_and_explains_it_on_login(): void
    {
        Exceptions::fake();
        $this->mock(PlatformMail::class)->shouldReceive('welcome')->once()->andThrow(new RuntimeException('Queue indisponible'));
        $this->registration()->call('register')->assertHasNoErrors()->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['email' => 'owner@example.com']);
        $this->assertDatabaseCount('tenants', 1);
        $this->assertStringContainsString('Le mail de bienvenue n’a pas pu être programmé', session('success'));
        Exceptions::assertReported(RuntimeException::class);
    }

    private function registration(): Testable
    {
        return Livewire::test(Register::class)->set('name', 'Abdoulaye')->set('business_name', 'Mon espace')
            ->set('phone', '626427442')->set('email', 'owner@example.com')
            ->set('password', 'SecurePass1!')->set('password_confirmation', 'SecurePass1!')->set('terms', true);
    }
}
