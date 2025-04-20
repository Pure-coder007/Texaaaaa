<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class RedirectClientToOnboarding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Register the URL macro if it doesn't exist
        if (!URL::hasMacro('livewire_current')) {
            URL::macro('livewire_current', function () {
                if (request()->route() && request()->route()->named('livewire.update')) {
                    $previousUrl = url()->previous(); // use the more meaningful one from previous
                    return $previousUrl;
                } else {
                    if (request()->route()) {
                        return request()->fullUrl();
                    }
                    return 'No route available';
                }
            });
        }

        // Try multiple ways to get the authenticated user
        $user = $request->user() ?? Auth::user();

        // Get the current URL using our macro
        $currentUrl = url()->livewire_current();
        $baseUrl = config('app.url');
        $logoutURL =  $baseUrl . '/client/logout';

        // Only process if there's a logged-in user
        if ($user) {
            // Check if user is a client
            if ($user->role === 'client' && !$user->onboarding_completed) {
                // Don't redirect if already on the onboarding page or authentication routes
                $onboardingUrl = route('filament.client.pages.client-onboarding');

                // Check if the current URL is not already the onboarding URL or other excluded routes
                if ($currentUrl !== $onboardingUrl && $currentUrl !== $logoutURL &&
                    !$request->is('livewire/update*') &&
                    !$request->is('login*') &&
                    !$request->is('register*') &&
                    !$request->is('logout*') &&
                    !$request->is('password*')) {

                    return redirect()->route('filament.client.pages.client-onboarding');
                }
            }
        }

        return $next($request);
    }
}
