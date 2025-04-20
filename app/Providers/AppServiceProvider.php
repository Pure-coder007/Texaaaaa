<?php

namespace App\Providers;

use App\Models\ClientDocument;
use App\Models\Inspection;
use App\Models\Payment;
use App\Models\PboSale;
use App\Models\Purchase;
use App\Models\User;
use App\Observers\ClientDocumentObserver;
use App\Observers\InspectionObserver;
use App\Observers\PaymentObserver;
use App\Observers\PboSaleObserver;
use App\Observers\PurchaseObserver;
use App\Observers\UserObserver;
use App\Settings\SystemSettings;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        parent::register();
        FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/js/app.js')"));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom colors for client panel
        FilamentColor::register([
            'primary' => Color::hex('#1f2a6a'),
            'secondary' => Color::hex('#fa0200'),
            'cream' => Color::hex('#f8f7f2'),
        ]);
        //
        Gate::define('viewApiDocs', function (User $user) {
            return true;
        });
        // Gate::policy()
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Google\Provider::class);
        });

        View::share('systemSettings', app(SystemSettings::class));

        Gate::before(function ($user, $ability) {
            if ($user->role == 'client' || $user->role == 'pbo' ) {
                return true;
            }

            // Otherwise, continue with normal permission checks
            return null;
        });

        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn (): string => view('filament.marketplace-link')->render(),
        );

        Purchase::observe(PurchaseObserver::class);
        Payment::observe(PaymentObserver::class);
        Inspection::observe(InspectionObserver::class);
        ClientDocument::observe(ClientDocumentObserver::class);
        User::observe(UserObserver::class);
        PboSale::observe(PboSaleObserver::class);
    }
}
