<?php

namespace App\Observers;

use App\Models\Subscription;
use App\PlatformNotifications;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Throwable;

class PlatformSubscriptionObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Subscription $subscription): void
    {
        $this->notify($subscription, true);
    }

    public function updated(Subscription $subscription): void
    {
        if ($subscription->wasChanged(['status', 'amount', 'currency', 'starts_at', 'ends_at', 'trial_ends_at'])) {
            $this->notify($subscription, false);
        }
    }

    private function notify(Subscription $subscription, bool $created): void
    {
        try {
            app(PlatformNotifications::class)->subscriptionChanged($subscription, $created);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
