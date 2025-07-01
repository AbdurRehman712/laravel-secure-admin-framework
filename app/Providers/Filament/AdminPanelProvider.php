<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->authGuard('admin')
            ->brandName('SecureAdmin')
            ->brandLogo(asset('images/logo.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/favicon.svg'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverResources(in: base_path('Modules/Core/app/Filament/Resources'), for: 'Modules\Core\app\Filament\Resources')
            ->discoverResources(in: base_path('Modules/PublicUser/app/Filament/Resources'), for: 'Modules\PublicUser\app\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')


            // Auto-discover all module resources
            ->tap(function ($panel) {
                $this->discoverModuleResources($panel);
            })

            ->discoverPages(in: base_path('Modules/ModuleBuilder/app/Filament/Pages'), for: 'Modules\ModuleBuilder\app\Filament\Pages')
            ->pages([
                Dashboard::class,
                \Modules\ModuleBuilder\app\Filament\Pages\EnhancedModuleBuilder::class,
                \Modules\ModuleBuilder\app\Filament\Pages\ModuleEditor::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->renderHook(
                'panels::sidebar.nav.end',
                fn (): string => '
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            <div class="flex items-center justify-center space-x-2 mb-1">
                                <svg width="16" height="19" viewBox="0 0 277 334" fill="currentColor" class="text-amber-600">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M64.8767 21.9673C25.9026 46.4741 0 89.862 0 139.3V333.2H277V139.3C277 62.8084 214.991 0.799805 138.5 0.799805C127.124 0.799805 116.069 2.17131 105.49 4.75798V333.2H64.8765L64.8767 21.9673ZM214.575 178.131C212.561 172.985 209.652 168.509 205.848 164.705C198.911 157.769 191.974 152.286 185.038 148.259C178.101 144.007 169.598 141.881 159.529 141.881C152.816 141.881 145.879 143 138.719 145.238C131.782 147.476 125.293 150.161 119.251 153.293V179.138C124.174 176.9 129.209 175.334 134.355 174.439C139.726 173.32 143.977 172.761 147.11 172.761C154.718 172.761 160.647 174.327 164.899 177.46C169.151 180.369 172.507 184.508 174.968 189.879C177.43 195.249 178.996 201.403 179.667 208.339C180.339 215.276 180.674 222.548 180.674 230.156V333.2H221.623V226.8C221.623 221.43 221.511 215.947 221.288 210.353C221.064 204.759 220.392 199.277 219.274 193.907C218.379 188.312 216.812 183.054 214.575 178.131Z"/>
                                </svg>
                                <span class="font-medium">SecureAdmin Framework</span>
                            </div>
                            <div>Powered by <strong class="text-amber-600">H. Sol</strong></div>
                            <div class="text-[10px] text-gray-400">(Hereafter Solutions)</div>
                        </div>
                    </div>
                '
            )
            ->renderHook(
                'panels::auth.login.form.after',
                fn (): string => '
                    <div class="text-center mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <svg width="16" height="19" viewBox="0 0 277 334" fill="currentColor" class="text-amber-600">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M64.8767 21.9673C25.9026 46.4741 0 89.862 0 139.3V333.2H277V139.3C277 62.8084 214.991 0.799805 138.5 0.799805C127.124 0.799805 116.069 2.17131 105.49 4.75798V333.2H64.8765L64.8767 21.9673ZM214.575 178.131C212.561 172.985 209.652 168.509 205.848 164.705C198.911 157.769 191.974 152.286 185.038 148.259C178.101 144.007 169.598 141.881 159.529 141.881C152.816 141.881 145.879 143 138.719 145.238C131.782 147.476 125.293 150.161 119.251 153.293V179.138C124.174 176.9 129.209 175.334 134.355 174.439C139.726 173.32 143.977 172.761 147.11 172.761C154.718 172.761 160.647 174.327 164.899 177.46C169.151 180.369 172.507 184.508 174.968 189.879C177.43 195.249 178.996 201.403 179.667 208.339C180.339 215.276 180.674 222.548 180.674 230.156V333.2H221.623V226.8C221.623 221.43 221.511 215.947 221.288 210.353C221.064 204.759 220.392 199.277 219.274 193.907C218.379 188.312 216.812 183.054 214.575 178.131Z"/>
                            </svg>
                            <span class="font-medium">SecureAdmin Framework</span>
                        </div>
                        <div class="flex items-center justify-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                            <span>Powered by <strong class="text-amber-600 dark:text-amber-400">H. Sol</strong></span>
                            <span class="text-xs">(Hereafter Solutions)</span>
                        </div>
                    </div>
                '
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

    private function discoverModuleResources($panel): void
    {
        $modulesPath = base_path('Modules');

        if (!file_exists($modulesPath)) {
            return;
        }

        $directories = glob($modulesPath . '/*', GLOB_ONLYDIR);

        foreach ($directories as $directory) {
            $moduleName = basename($directory);

            // Skip system modules
            if (in_array($moduleName, ['Core', 'PublicUser', 'ModuleBuilder'])) {
                continue;
            }

            $resourcesPath = $directory . '/app/Filament/Resources';

            if (file_exists($resourcesPath)) {
                $panel->discoverResources(
                    in: $resourcesPath,
                    for: "Modules\\{$moduleName}\\app\\Filament\\Resources"
                );
            }
        }
    }
}
