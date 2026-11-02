<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SubscriptionPaymentController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenantId = $request->user()->activeTenantId();
        $tenant = $request->user()->tenant;
        $trialStart = CarbonImmutable::instance($tenant->created_at);
        $trialEnd = $trialStart->addMonthNoOverflow();
        $subscription = Subscription::query()->where('tenant_id', $tenantId)->latest('id')->first();
        $state = $subscription?->status === 'active' && $subscription->ends_at?->isFuture()
            ? 'active' : (now()->lt($trialEnd) ? 'trial' : 'expired');
        $remainingDays = max(0, (int) ceil(now()->diffInDays($trialEnd, false)));
        $annualStart = match (true) {
            $state === 'trial' => $trialEnd,
            $state === 'active' => CarbonImmutable::instance($subscription->ends_at),
            default => CarbonImmutable::today(),
        };
        $annualEnd = $annualStart->addYearNoOverflow();

        return view('payments.show', compact('state', 'trialStart', 'trialEnd', 'remainingDays', 'annualStart', 'annualEnd'));
    }
}
