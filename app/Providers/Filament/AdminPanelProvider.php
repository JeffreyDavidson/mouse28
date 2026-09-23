<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Widgets\ContentCalendar;
use App\Filament\Widgets\InspirationWidget;
use App\Filament\Widgets\QuickDraft;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\WelcomeBanner;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $selectAccessibilityScript = Vite::asset('resources/js/filament/select-accessibility.js');

        if (! Vite::isRunningHot()) {
            $selectAccessibilityScript = parse_url($selectAccessibilityScript, PHP_URL_PATH) ?: $selectAccessibilityScript;
        }

        $selectAccessibilityScriptTag = '<script type="module" src="'.e($selectAccessibilityScript).'"></script>';

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Mouse28')
            ->brandLogo(view('filament.brand-logo'))
            ->darkModeBrandLogo(view('filament.brand-logo'))
            ->darkMode(false)
            ->font('Poppins', provider: LocalFontProvider::class)
            ->login(Login::class)
            ->profile()
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
            ], isRequired: fn (): bool => ! app()->isLocal())
            ->spa()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->assets([
                Js::make('select-accessibility')->module()->html($selectAccessibilityScriptTag),
            ])
            ->colors([
                'primary' => '#5b3e9e',
            ])
            ->navigationGroups([
                NavigationGroup::make('Content'),
                NavigationGroup::make('Communication'),
                NavigationGroup::make('Settings'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                WelcomeBanner::class,
                StatsOverview::class,
                RecentActivity::class,
                QuickDraft::class,
                ContentCalendar::class,
                InspirationWidget::class,
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
            ]);
    }
}
