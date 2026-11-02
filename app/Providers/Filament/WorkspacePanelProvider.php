<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\UserProfile;
use App\Models\PlatformSetting;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class WorkspacePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('workspace')
            ->path('workspace')
            ->viteTheme('resources/css/filament/workspace/theme.css')
            ->brandName(fn (): string => PlatformSetting::current()->name.' — Administration')
            ->brandLogo(fn (): string => PlatformSetting::current()->logoUrl())
            ->brandLogoHeight('3.5rem')
            ->renderHook(PanelsRenderHook::FOOTER, fn (): View => view('filament.partials.footer', [
                'platform' => PlatformSetting::current(),
            ]))
            ->favicon(fn (): string => PlatformSetting::current()->logoUrl())
            ->plugin(FilamentShieldPlugin::make()
                ->navigationGroup('Sécurité')
                ->navigationLabel('Rôles et permissions')
                ->navigationSort(6)
                ->modelLabel('rôle')
                ->pluralModelLabel('rôles'))
            ->login(Login::class)
            ->userMenuItems([
                'profile' => fn (Action $action): Action => $action->label('Mon profil')->url(UserProfile::getUrl()),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->databaseNotifications()
            ->databaseNotificationsPolling('15s')
            ->colors([
                'primary' => Color::hex('#c64b08'),
            ])
            ->font('Plus Jakarta Sans', provider: LocalFontProvider::class)
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): Htmlable => Vite::fonts('panel'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ], isPersistent: true);
    }
}
