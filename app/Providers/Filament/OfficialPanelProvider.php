<?php

namespace App\Providers\Filament;

use App\Models\Barangay;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OfficialPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('official')
            ->path('official')
            ->databaseNotifications()
            ->databaseNotificationsPolling(null)
            ->brandLogo(
                fn() =>
                view(
                    'components.application-logo',
                    ['attributes' => 'h-12 w-12']
                )
            )
            ->topNavigation()
            ->login()
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label('My Profile')
                    ->url(fn(): string => \App\Filament\Official\Pages\Profile::getUrl())
                    ->icon('heroicon-o-user-circle'),
            ])
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->tenant(Barangay::class, slugAttribute: 'barangay_code')
            ->discoverResources(in: app_path('Filament/Official/Resources'), for: 'App\\Filament\\Official\\Resources')
            ->discoverPages(in: app_path('Filament/Official/Pages'), for: 'App\\Filament\\Official\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Official/Widgets'), for: 'App\\Filament\\Official\\Widgets')
            ->widgets([
                \App\Filament\Official\Widgets\WelcomeOfficial::class,
            ])
            ->discoverLivewireComponents(in: app_path('Livewire'), for: 'App\\Livewire')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                'panels::head.end',
                fn(): string => view('filament.official.hooks.vite')->render(),
            );
    }
}
