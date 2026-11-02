<?php

namespace App\Jobs;

use App\Mail\SubscriptionExpired;
use App\Mail\TenantCommunication;
use App\Mail\Welcome;
use App\Models\EmailDelivery;
use App\Models\Scopes\TenantScope;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Mail;
use LogicException;
use Throwable;

class SendPlatformMail implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $maxExceptions = 3;

    public int $timeout = 45;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public function __construct(public int $deliveryId) {}

    /** @return list<WithoutOverlapping> */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('email-delivery:'.$this->deliveryId))->releaseAfter(10)->expireAfter(90)];
    }

    public function handle(): void
    {
        $delivery = EmailDelivery::query()->find($this->deliveryId);
        if ($delivery === null || in_array($delivery->status, ['accepted', 'failed', 'skipped'], true)) {
            return;
        }
        $recipient = User::query()->with('tenant')->find($delivery->recipient_id);
        if (! $this->canSend($delivery, $recipient)) {
            $delivery->update(['status' => 'skipped', 'last_error' => 'Le destinataire ou les conditions d’envoi ont changé.']);

            return;
        }
        $delivery->update(['status' => 'processing', 'attempts' => $delivery->attempts + 1, 'last_error' => null]);
        try {
            Mail::to($delivery->email)->send($this->mailable($delivery));
        } catch (Throwable $exception) {
            $delivery->update(['status' => 'queued', 'last_error' => 'Une nouvelle tentative est programmée.']);
            throw $exception;
        }
        $delivery->update(['status' => 'accepted', 'sent_at' => now(), 'last_error' => null]);
    }

    private function canSend(EmailDelivery $delivery, ?User $recipient): bool
    {
        if ($recipient === null || $recipient->tenant === null || (int) $recipient->tenant_id !== (int) $delivery->tenant_id
            || $recipient->email !== $delivery->email || $recipient->role !== 'owner') {
            return false;
        }
        if ($delivery->kind === 'communication') {
            if (! User::query()->find($delivery->sender_id)?->isPlatformAdmin()) {
                return false;
            }

            return match ($delivery->payload['mode'] ?? '') {
                'individual' => true,
                'marketing' => $recipient->is_active && $recipient->tenant->is_active,
                default => false,
            };
        }
        if (! $recipient->is_active || ! $recipient->tenant->is_active) {
            return false;
        }
        if ($delivery->kind === 'subscription_expired') {
            $latest = Subscription::withoutGlobalScope(TenantScope::class)->where('tenant_id', $delivery->tenant_id)->latest('id')->first();
            if ($latest?->status === 'active' && $latest->ends_at?->isFuture()) {
                return false;
            }

            return CarbonImmutable::parse($delivery->payload['expiredAt'])->lte(now());
        }

        return true;
    }

    private function mailable(EmailDelivery $delivery): Mailable
    {
        $data = $delivery->payload;

        return match ($delivery->kind) {
            'welcome' => new Welcome($delivery->name, $delivery->business_name, $data['loginUrl']),
            'communication' => new TenantCommunication($delivery->name, $delivery->business_name, $data['subject'], $data['messageBody'], $data['platformName'], $data['buttonLabel'], $data['buttonUrl']),
            'subscription_expired' => new SubscriptionExpired($delivery->name, $delivery->business_name, CarbonImmutable::parse($data['expiredAt']), $data['paymentUrl'], $data['isTrial']),
            default => throw new LogicException('Type de mail non pris en charge.'),
        };
    }

    public function failed(?Throwable $exception): void
    {
        EmailDelivery::query()->whereKey($this->deliveryId)->whereNotIn('status', ['accepted', 'skipped'])->update([
            'status' => 'failed', 'failed_at' => now(), 'last_error' => 'Le mail n’a pas pu être envoyé après plusieurs tentatives.',
        ]);
    }
}
