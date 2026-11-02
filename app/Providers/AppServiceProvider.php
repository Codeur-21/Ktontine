<?php

namespace App\Providers;

use App\Http\Middleware\EnsureTenantAccess;
use App\Models\PlatformSetting;
use App\Models\Subscription;
use App\Models\User;
use App\Observers\PlatformSubscriptionObserver;
use App\Observers\PlatformUserObserver;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(PlatformUserObserver::class);
        Subscription::observe(PlatformSubscriptionObserver::class);

        View::composer([
            'welcome', 'components.layouts.auth-layout', 'components.layouts.backend', 'tickets.sheet',
        ], function (ViewInstance $view): void {
            $platform = PlatformSetting::current();
            $view->with('platformLogoUrl', $platform->logoUrl());
            $whatsappNumber = preg_replace('/[^0-9]/', '', $platform->contact_phone ?? '');
            if (str_starts_with($whatsappNumber, '00')) {
                $whatsappNumber = substr($whatsappNumber, 2);
            }
            if (strlen($whatsappNumber) === 9) {
                $whatsappNumber = '224'.$whatsappNumber;
            }

            $view->with('supportContact', [
                'name' => $platform->name,
                'phone' => $platform->contact_phone,
                'whatsappNumber' => $whatsappNumber,
                'email' => $platform->contact_email,
                'address' => $platform->address,
            ]);
        });

        Gate::policy(Role::class, RolePolicy::class);

        Gate::before(fn (User $user, string $ability): ?bool => $user->isPlatformAdmin() ? true : null);

        Gate::define('view-platform-administration', fn (User $user): bool => $user->isPlatformAdmin());
        Livewire::addPersistentMiddleware([EnsureTenantAccess::class]);
    }
}
