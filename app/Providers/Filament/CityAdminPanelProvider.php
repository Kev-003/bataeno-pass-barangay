<?php

namespace App\Providers\Filament;

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
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

//Models
use App\Models\Barangay;
use App\Models\Municipality;

class CityAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('city')
            ->path('city-admin')
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
                'primary' => Color::Green,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn (): string => Blade::render('<div class="hidden md:block mr-4"><x-demo-mode-switcher /></div>'),
            )
            ->tenant(model: Municipality::class, slugAttribute: 'municity_code')
            ->discoverResources(in: app_path('Filament/CityAdmin/Resources'), for: 'App\\Filament\\CityAdmin\\Resources')
            ->discoverPages(in: app_path('Filament/CityAdmin/Pages'), for: 'App\\Filament\\CityAdmin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            
            ->discoverWidgets(in: app_path('Filament/CityAdmin/Widgets'), for: 'App\\Filament\\CityAdmin\\Widgets')
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
            )
            ->renderHook(
                'panels::scripts.after',
                fn(): string => '<script src="https://cdnjs.cloudflare.com/ajax/libs/qr-scanner/1.4.2/qr-scanner.umd.min.js"></script>' .
                '<script>QrScanner.WORKER_PATH = "https://cdnjs.cloudflare.com/ajax/libs/qr-scanner/1.4.2/qr-scanner-worker.min.js";</script>',
            );
    }
}
