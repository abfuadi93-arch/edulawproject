<?php

namespace App\Providers\Filament;

use App\Models\SiteSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Edulaw Dashboard')
            ->brandLogo(function (): HtmlString {
                $siteSettings = SiteSetting::publicValues();
                $footerLogo = SiteSetting::assetUrl($siteSettings['site.footer_logo'] ?? null, 'images/logo/edulaw-logo.png');

                return new HtmlString('
                    <span class="edulaw-admin-brand-logo-wrap">
                        <img src="'.e($footerLogo).'" alt="Edulaw Project" class="edulaw-admin-brand-logo">
                    </span>
                ');
            })
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/logo/icon-logo.png'))
            ->colors([
                'primary' => Color::hex('#2563eb'),
                'gray' => Color::Slate,
            ])
            ->globalSearch()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchFieldSuffix('META+K')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::Full)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                'Konten Website',
                'Interaksi',
                'Referensi Konten',
                'Pengaturan Website',
                'Akses Admin',
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Lihat Website')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(url('/'), true)
                    ->sort(5),
            ])
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn () => view('filament.topbar-website-link')
            )
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )
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
            ]);
    }
}
