<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Http\Middleware\RedirectToAllowedPanel;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->login()
            ->passwordReset()
            ->profile()
            ->id('admin')
            ->path('admin')
            ->brandName('Helpdesk System')
            ->brandLogo(asset('branding/logo-tag.svg'))
            ->brandLogoHeight('4rem')
            ->favicon(asset('branding/logo.svg'))
            ->colors([
                'primary' => Color::Blue,
                'pending' => Color::Amber,
                'chat' => Color::Cyan,
                'update' => Color::Indigo,
                'delete' => Color::Red,
                'process' => Color::Orange,
                'approve' => Color::Green,
                'reject' => Color::Red,
                'reopen' => Color::Yellow,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('5rem')
            ->maxContentWidth('full')
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\Filament\Resources'
            )
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->homeUrl(fn () => match (auth()->user()?->getRoleNames()->first()) {
                default => '/admin',
            })
            ->authMiddleware([
                Authenticate::class,
                'auth',
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): View => view('components.helpdesk-realtime-notification')
            )
            ->navigationGroups([
                NavigationGroup::make('Service Desk')
                    ->collapsed(true),
                NavigationGroup::make('Teknisi')
                    ->collapsed(true),
                NavigationGroup::make('Monitoring')
                    ->collapsed(true)
                    ->icon('heroicon-o-clock'),
                NavigationGroup::make('Laporan')
                    ->collapsed(true)
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make('Master Data')
                    ->collapsed(true),
                NavigationGroup::make('Filament Shield')
                    ->collapsed(true)
                    ->icon('heroicon-o-shield-check'),

            ]);

    }
}
