<?php

namespace App;

use App\Jobs\SendPlatformMail;
use App\Models\EmailDelivery;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlatformMail
{
    public function welcome(User $recipient, string $businessName, string $loginUrl): EmailDelivery
    {
        return $this->queue($recipient, 'welcome', ['loginUrl' => $loginUrl], 'welcome:'.$recipient->id, $businessName);
    }

    public function subscriptionExpired(User $recipient, CarbonImmutable $expiredAt, string $paymentUrl, bool $isTrial = false): EmailDelivery
    {
        return $this->queue($recipient, 'subscription_expired', [
            'expiredAt' => $expiredAt->toIso8601String(), 'paymentUrl' => $paymentUrl, 'isTrial' => $isTrial,
        ], 'expired:'.$recipient->id.':'.$expiredAt->toIso8601String().':'.($isTrial ? 'trial' : 'subscription'), $recipient->tenant->name);
    }

    /** @param array<string, mixed> $prepared
     * @return Collection<int, EmailDelivery>
     */
    public function communication(User $sender, array $prepared): Collection
    {
        abort_unless($sender->fresh()?->isPlatformAdmin(), 403);

        return DB::transaction(function () use ($sender, $prepared): Collection {
            return collect($prepared['recipients'])->map(function (array $recipient) use ($sender, $prepared): EmailDelivery {
                $user = User::query()->where('role', 'owner')->where('tenant_id', $recipient['tenant_id'])
                    ->where('email', $recipient['email'])->findOrFail($recipient['id']);

                return $this->queue($user, 'communication', [
                    'mode' => $prepared['mode'], 'subject' => $prepared['subject'], 'messageBody' => $prepared['messageBody'],
                    'platformName' => $prepared['platformName'], 'buttonLabel' => $prepared['buttonLabel'], 'buttonUrl' => $prepared['buttonUrl'],
                ], 'communication:'.$prepared['token'].':'.$user->id, $recipient['businessName'], $sender->id, $prepared['token']);
            });
        });
    }

    /** @param array<string, mixed> $payload */
    private function queue(User $recipient, string $kind, array $payload, string $key, string $businessName, ?int $senderId = null, ?string $batchId = null): EmailDelivery
    {
        return DB::transaction(function () use ($recipient, $kind, $payload, $key, $businessName, $senderId, $batchId): EmailDelivery {
            $delivery = EmailDelivery::query()->firstOrCreate(['dedup_key' => hash('sha256', $key)], [
                'batch_id' => $batchId, 'sender_id' => $senderId, 'recipient_id' => $recipient->id,
                'tenant_id' => $recipient->tenant_id, 'kind' => $kind, 'status' => 'queued',
                'email' => $recipient->email, 'name' => $recipient->name, 'business_name' => $businessName, 'payload' => $payload,
            ]);
            if ($delivery->wasRecentlyCreated) {
                SendPlatformMail::dispatch($delivery->id)->onQueue('emails')->afterCommit();
            }

            return $delivery;
        });
    }
}
