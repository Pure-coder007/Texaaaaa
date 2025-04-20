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
use App\Filament\Pages\Login;
use App\Filament\Pages\Register;
use App\Http\Middleware\CheckPanelRole;
use Filament\Forms\Components\FileUpload;
use Filament\View\PanelsRenderHook;
use Jeffgreco13\FilamentBreezy\BreezyCore;



class PboPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pbo')
            ->path('/pbo')
            ->discoverResources(in: app_path('Filament/Pbo/Resources'), for: 'App\\Filament\\Pbo\\Resources')
            ->when($this->settings->login_enabled ?? true, fn($panel) => $panel->login(Login::class))
            ->when(fn($panel) => $panel->passwordReset())
            ->when($this->settings->registration_enabled ?? true, fn($panel) => $panel->registration(Register::class))
            ->discoverPages(in: app_path('Filament/Pbo/Pages'), for: 'App\\Filament\\Pbo\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Pbo/Widgets'), for: 'App\\Filament\\Pbo\\Widgets')
            ->brandLogo(fn () => view('filament.logo'))
            ->favicon(asset('favicon.png'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class
            ])
            ->authGuard('pbo')
            ->font('DM Sans')
            ->plugins(
                $this->getPlugins()
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
    private function getPlugins(): array
    {
        $plugins = [
            BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true, // Sets the 'account' link in the panel User Menu (default = true)
                    shouldRegisterNavigation: false, // Adds a main navigation item for the My Profile page (default = false)
                    navigationGroup: 'Account', // Sets the navigation group for the My Profile page (default = null)
                    hasAvatars: true, // Enables the avatar upload form component (default = false)
                    slug: 'my-profile'
                )
                ->avatarUploadComponent(fn($fileUpload) => $fileUpload->disableLabel())
                // OR, replace with your own component
                ->avatarUploadComponent(
                    fn() => FileUpload::make('avatar_url')
                        ->image()
                        ->disk('public')
                )
                ->enableTwoFactorAuthentication(false),
        ];

        return $plugins;
    }
}
