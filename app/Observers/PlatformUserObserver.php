<?php

namespace App\Observers;

use App\Models\User;
use App\PlatformNotifications;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Throwable;

class PlatformUserObserver implements ShouldHandleEventsAfterCommit
{
    public function created(User $user): void
    {
        try {
            app(PlatformNotifications::class)->ownerRegistered($user);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
