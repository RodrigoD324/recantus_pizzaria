<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
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
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->font('Inter')
            ->brandName("Recantu's")
            ->favicon(asset('assets/icons/pizza.ico'))
            ->login(Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
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
            ->renderHook(
                'panels::head.end',
                fn(): string => Blade::render("
            <style>
                /* Estilização da Sidebar */
                .fi-sidebar {
                    background-color: #1e293b !important;
                    border-right: 1px solid #334155;
                }

                /* Cor do texto dos itens do menu */
                .fi-sidebar-item-label, .fi-sidebar-group-label {
                    color: #f1f5f9 !important;
                }

                /* Cor dos ícones */
                .fi-sidebar-item-icon {
                    color: #94a3b8 !important;
                }

                /* Efeito de Hover e Item Ativo */
                .fi-sidebar-item-button:hover, 
                .fi-sidebar-item-active,
                .fi-sidebar-item-button.fi-active {
                    background-color: #334155 !important;
                }
                
                /* Logo/Nome da marca no topo da sidebar */
                .fi-sidebar-header {
                    background-color: #0f172a !important;
                    border-bottom: 1px solid #1e293b;
                }
            </style>
        "),
            );
    }
}
