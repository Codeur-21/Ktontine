<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Scopes\TenantScope;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Gate;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    public function getTitle(): string
    {
        return 'Vue d’ensemble';
    }

    public function getSubheading(): ?string
    {
        return 'Pilotez les tontiniers, les comptes et les abonnements Ktontine.';
    }

    protected function getViewData(): array
    {
        Gate::authorize('view-platform-administration');
        $activeSubscriptions = Subscription::withoutGlobalScope(TenantScope::class)->where('status', 'active');

        return [
            'stats' => [
                'tenants' => Tenant::query()->count(),
                'activeTenants' => Tenant::query()->where('is_active', true)->count(),
                'users' => User::query()->count(),
                'activeSubscriptions' => (clone $activeSubscriptions)->count(),
                'annualRevenue' => (int) (clone $activeSubscriptions)->sum('amount'),
                'trialsEndingSoon' => Subscription::withoutGlobalScope(TenantScope::class)
                    ->where('status', 'trialing')
                    ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
                    ->count(),
                'pastDue' => Subscription::withoutGlobalScope(TenantScope::class)->where('status', 'past_due')->count(),
            ],
            'recentTenants' => Tenant::query()
                ->withCount('users')
                ->latest()
                ->limit(5)
                ->get(),
            'recentSubscriptions' => Subscription::withoutGlobalScope(TenantScope::class)
                ->with('tenant:id,name')
                ->latest()
                ->limit(5)
                ->get(),
            'urls' => [
                'tenants' => TenantResource::getUrl('index'),
                'createTenant' => TenantResource::getUrl('create'),
                'users' => UserResource::getUrl('index'),
                'createUser' => UserResource::getUrl('create'),
                'subscriptions' => SubscriptionResource::getUrl('index'),
                'createSubscription' => SubscriptionResource::getUrl('create'),
            ],
        ];
    }
}
