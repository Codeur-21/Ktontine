<?php

namespace Tests\Feature;

use App\Jobs\SendPlatformMail;
use App\Mail\SubscriptionExpired;
use App\Mail\Welcome;
use App\Models\EmailDelivery;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\PlatformMail;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class QueuedPlatformMailTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        config(['queue.default' => 'database']);
        Mail::fake();
    }

    public function test_jobs_are_enqueued_after_commit_and_rolled_back_with_their_delivery(): void
    {
        $owner = $this->owner('alpha');
        DB::beginTransaction();
        $delivery = app(PlatformMail::class)->welcome($owner, 'alpha', route('login'));
        $this->assertDatabaseCount('jobs', 0);
        DB::commit();
        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseHas('jobs', ['queue' => 'emails']);
        $same = app(PlatformMail::class)->welcome($owner, 'alpha', route('login'));
        $this->assertSame($delivery->id, $same->id);
        $this->assertDatabaseCount('jobs', 1);
        $second = $this->owner('beta');
        DB::beginTransaction();
        app(PlatformMail::class)->welcome($second, 'beta', route('login'));
        DB::rollBack();
        $this->assertDatabaseCount('email_deliveries', 1);
        $this->assertDatabaseCount('jobs', 1);
        Mail::assertNothingSent();
    }

    public function test_welcome_job_records_acceptance_and_does_not_send_again(): void
    {
        $owner = $this->owner('alpha');
        $delivery = app(PlatformMail::class)->welcome($owner, 'alpha', route('login'));
        $job = new SendPlatformMail($delivery->id);
        $job->handle();
        $job->handle();
        $job->failed(new RuntimeException('Retard de confirmation'));
        Mail::assertSent(Welcome::class, 1);
        Mail::assertSent(Welcome::class, fn (Welcome $mail): bool => $mail->hasTo($owner->email));
        $this->assertSame('accepted', $delivery->fresh()->status);
        $this->assertSame(1, $delivery->fresh()->attempts);
        $this->assertNotNull($delivery->fresh()->sent_at);
    }

    public function test_smtp_exception_is_rethrown_for_retry_and_terminal_failure_is_recorded(): void
    {
        $owner = $this->owner('alpha');
        $delivery = app(PlatformMail::class)->welcome($owner, 'alpha', route('login'));
        $pending = \Mockery::mock(PendingMail::class);
        $pending->shouldReceive('send')->once()->andThrow(new RuntimeException('SMTP indisponible'));
        Mail::shouldReceive('to')->once()->with($owner->email)->andReturn($pending);
        $job = new SendPlatformMail($delivery->id);
        try {
            $job->handle();
            $this->fail('Le job doit signaler l’échec au worker.');
        } catch (RuntimeException $exception) {
            $this->assertSame('SMTP indisponible', $exception->getMessage());
        }
        $this->assertSame('queued', $delivery->fresh()->status);
        $this->assertSame(1, $delivery->fresh()->attempts);
        $job->failed(new RuntimeException('SMTP indisponible'));
        $this->assertSame('failed', $delivery->fresh()->status);
        $this->assertNotNull($delivery->fresh()->failed_at);
    }

    public function test_changed_recipient_address_cancels_delivery_instead_of_sending_to_old_address(): void
    {
        $owner = $this->owner('alpha');
        $delivery = app(PlatformMail::class)->welcome($owner, 'alpha', route('login'));
        $owner->update(['email' => 'new-address@example.com']);
        (new SendPlatformMail($delivery->id))->handle();
        $this->assertSame('skipped', $delivery->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_marketing_is_cancelled_for_newly_inactive_recipient_but_personal_message_can_be_sent(): void
    {
        $owner = $this->owner('alpha');
        $admin = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        $marketing = $this->communication($admin, $owner, 'marketing');
        $owner->update(['is_active' => false]);
        (new SendPlatformMail($marketing->id))->handle();
        $this->assertSame('skipped', $marketing->fresh()->status);
        Mail::assertNothingSent();
        $individual = $this->communication($admin, $owner, 'individual');
        (new SendPlatformMail($individual->id))->handle();
        $this->assertSame('accepted', $individual->fresh()->status);
        Mail::assertSentCount(1);
    }

    public function test_disabled_sender_cannot_send_a_previously_queued_communication(): void
    {
        $owner = $this->owner('alpha');
        $admin = UserFactory::new()->create(['tenant_id' => null, 'role' => 'owner', 'is_active' => true]);
        $delivery = $this->communication($admin, $owner, 'individual');
        $admin->update(['is_active' => false]);
        (new SendPlatformMail($delivery->id))->handle();
        $this->assertSame('skipped', $delivery->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_expiration_mail_is_sent_by_job_and_skipped_if_subscription_was_renewed(): void
    {
        $owner = $this->owner('alpha');
        $delivery = app(PlatformMail::class)->subscriptionExpired($owner, CarbonImmutable::now()->subDay(), route('abonnement'), isTrial: true);
        (new SendPlatformMail($delivery->id))->handle();
        Mail::assertSent(SubscriptionExpired::class, fn (SubscriptionExpired $mail): bool => $mail->hasTo($owner->email) && $mail->isTrial);
        $second = $this->owner('beta');
        $cancelled = app(PlatformMail::class)->subscriptionExpired($second, CarbonImmutable::now()->subDay(), route('abonnement'));
        $this->actingAs($second);
        Subscription::query()->create(['tenant_id' => $second->tenant_id, 'status' => 'active', 'ends_at' => now()->addYear(), 'amount' => 200000, 'currency' => 'GNF']);
        (new SendPlatformMail($cancelled->id))->handle();
        $this->assertSame('skipped', $cancelled->fresh()->status);
        Mail::assertSentCount(1);
    }

    private function owner(string $slug): User
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);

        return UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
    }

    private function communication(User $admin, User $owner, string $mode): EmailDelivery
    {
        return app(PlatformMail::class)->communication($admin, [
            'token' => (string) Str::uuid(), 'mode' => $mode, 'platformName' => 'Ktontine',
            'subject' => 'Bonjour {nom}', 'messageBody' => 'Un message pour {espace}.', 'buttonLabel' => '', 'buttonUrl' => '',
            'recipients' => [['id' => $owner->id, 'tenant_id' => $owner->tenant_id, 'email' => $owner->email, 'businessName' => $owner->tenant->name]],
        ])->first();
    }
}
