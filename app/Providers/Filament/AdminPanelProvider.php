<?php

namespace App\Providers\Filament;

use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Outerweb\FilamentTranslatableFields\Filament\Plugins\FilamentTranslatableFieldsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->defaultThemeMode(ThemeMode::Light)
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('50px')
            ->favicon(asset('favicon.ico'))
            ->colors(colors())
            ->id('admin')
            ->path('admin')
            ->login()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationItems([
                NavigationItem::make('support-chat')
                    ->label('Support Chat')
                    ->badge(fn(): ?string => \App\Models\ChatRoom::support()->whereHas('messages', function ($query) {
                        $query->where('created_at', '>=', now()->subHour());
                    })->count() ?: null)
                    ->badgeTooltip('Manage customer support conversations in real-time.')
                    ->url(fn(): string => route('admin.support.chat.index'), shouldOpenInNewTab: false)
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->group('Support')
                    ->visible(fn(): bool => Auth::user()->isAdmin),

                NavigationItem::make('telescope')
                    ->label('Telescope')
                    ->badge(fn(): string => '●')
                    ->badgeTooltip('Telescope helps track what happens behind the scenes in your app.')
                    ->url(fn(): string => app()->environment('local') ? route('telescope') : '#', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-chart-bar-square')
                    ->group('Monitor')
                    ->visible(fn(): bool => !app()->environment('testing') && app()->environment(['local', 'staging']) && Auth::user()->isAdmin),

                NavigationItem::make('pulse')
                    ->label('Pulse')
                    ->badge(fn(): string => '●')
                    ->badgeTooltip('Pulse provides real-time insights into your application\'s performance and health.')
                    ->url(fn(): string => route('pulse'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-heart')
                    ->group('Monitor')
                    ->visible(fn(): bool => !app()->environment('testing') && Auth::user()->isAdmin),

                NavigationItem::make('horizon')
                    ->label('Horizon')
                    ->badge(fn(): string => '●')
                    ->badgeTooltip('Horizon gives you a simple way to manage and monitor background tasks.')
                    ->url(fn(): string => route('horizon.index'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-lifebuoy')
                    ->group('Monitor')
                    ->visible(fn(): bool => !app()->environment('testing') && Auth::user()->isAdmin),
            ])
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
            ->plugins([
                FilamentTranslatableFieldsPlugin::make()
                    ->supportedLocales(config('app.supported_locales')),
                GlobalSearchModalPlugin::make(),
            ]);;
    }
}


function colors()
{
    return [
        'primary' => [
            50 => '#f0f9f2',
            100 => '#dcf2e1',
            200 => '#bce5c7',
            300 => '#8dd1a3',
            400 => '#4a9b60',
            500 => '#2a7c41',
            600 => '#1e5a2f',
            700 => '#1a4a28',
            800 => '#173d23',
            900 => '#14321e',
            950 => '#0a1b10',
        ],
        'secondary' => [
            50 => '#f0fdfc',
            100 => '#ccfbf1',
            200 => '#99f6e4',
            300 => '#5eead4',
            400 => '#2dd4bf',
            500 => '#03dac6',
            600 => '#0d9488',
            700 => '#018786',
            800 => '#134e4a',
            900 => '#134e4a',
            950 => '#042f2e',
        ],
    ];
}
