<?php

namespace App\Providers\Filament;

use App\Filament\Pemohon\Pages\Dashboard;
use App\Http\Middleware\IsPemohon;
use App\Http\Middleware\RedirectToAllowedPanel;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PemohonPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pemohon')
            ->path('pemohon')
            // Login pemohon memakai halaman custom di "/login" (LoginController),
            // bukan login bawaan panel. Menonaktifkan route login panel membuat
            // Filament::getLoginUrl() bernilai null, sehingga tamu yang mencoba
            // membuka panel dialihkan ke route('login') oleh konfigurasi bawaan
            // Laravel (redirectGuestsTo). Lihat juga IsPemohon::handle().
            ->login(null)
            ->passwordReset()
            ->profile()
            ->brandName('Helpdesk System')
            ->brandLogo(asset('branding/logo-tag.svg'))
            ->brandLogoHeight('4rem')
            ->favicon(asset('branding/logo.svg'))
            ->colors([
                'primary' => Color::Blue,
                'edit' => Color::Yellow,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('5rem')
            ->maxContentWidth('full')
            ->discoverResources(
                in: app_path('Filament/Pemohon/Resources'),
                for: 'App\\Filament\\Pemohon\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pemohon/Pages'),
                for: 'App\\Filament\\Pemohon\\Pages'
            )
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Pemohon/Widgets'),
                for: 'App\\Filament\\Pemohon\\Widgets'
            )
            ->widgets([
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
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
                RedirectToAllowedPanel::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                IsPemohon::class,
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): View => view('components.helpdesk-realtime-notification')
            )
            ->navigationGroups([
                NavigationGroup::make('Pelayanan')
                    ->collapsed(true)
                    ->icon('heroicon-o-wrench-screwdriver'),
                NavigationGroup::make('Monitoring')
                    ->collapsed(true)
                    ->icon('heroicon-o-clock'),
            ]);
    }
}
