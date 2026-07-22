<?php

namespace App\Providers\Filament;

use App\Filament\Pages\PerfilPortalPage;
use App\Filament\Resources\UserResource\Pages\RegisterUser;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\MenuItem;
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
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('')
            ->login()
            ->registration(RegisterUser::class)
            ->profile()
            ->userMenuItems([
                MenuItem::make()
                    ->label('Configuración')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn () => PerfilPortalPage::getUrl()),
            ])
            ->colors([
                'primary' => Color::hex('#BDECB6'),
                'danger'  => Color::hex('#FFC7C7'),
                'purple'  => Color::Purple,
            ])
            ->plugin(FilamentFullCalendarPlugin::make()->config([])->locale('es')->selectable(true))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
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
            ->viteTheme('resources/css/filament/dashboard/theme.css')
            ->topNavigation()
            // ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => Blade::render(<<<'BLADE'
                    <script>
                        document.addEventListener('modal-closed', () => {
                            queueMicrotask(() => {
                                if (! document.querySelector('.fi-modal.fi-modal-open')) {
                                    document.documentElement.style.overflow = '';
                                    document.body.style.overflow = '';
                                }
                            });
                        }, true);
                    </script>
                BLADE),
            );
    }
}
