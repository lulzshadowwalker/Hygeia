<?php

namespace App\Providers\Filament;

use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Enums\ThemeMode;
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
        'success' => [
            50 => '#f0f9ff',
            100 => '#e0f2fe',
            200 => '#bae6fd',
            300 => '#7dd3fc',
            400 => '#38bdf8',
            500 => '#4caf50',
            600 => '#0284c7',
            700 => '#0369a1',
            800 => '#075985',
            900 => '#0c4a6e',
            950 => '#082f49',
        ],
        'warning' => [
            50 => '#fffbeb',
            100 => '#fef3c7',
            200 => '#fde68a',
            300 => '#fcd34d',
            400 => '#fbbf24',
            500 => '#ff9800',
            600 => '#d97706',
            700 => '#b45309',
            800 => '#92400e',
            900 => '#78350f',
            950 => '#451a03',
        ],
        'danger' => [
            50 => '#fef2f2',
            100 => '#fee2e2',
            200 => '#fecaca',
            300 => '#fca5a5',
            400 => '#f87171',
            500 => '#f44336',
            600 => '#dc2626',
            700 => '#b91c1c',
            800 => '#991b1b',
            900 => '#7f1d1d',
            950 => '#450a0a',
        ],
        'info' => [
            50 => '#eff6ff',
            100 => '#dbeafe',
            200 => '#bfdbfe',
            300 => '#93c5fd',
            400 => '#60a5fa',
            500 => '#2196f3',
            600 => '#2563eb',
            700 => '#1d4ed8',
            800 => '#1e40af',
            900 => '#1e3a8a',
            950 => '#172554',
        ],
    ];
}
