<?php

namespace App\Filament\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\View\View;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.login';

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        // Check if user exists and was created through social login
        $user = \App\Models\User::where('email', $data['email'])->first();
        if ($user && is_null($user->password)) {
            throw ValidationException::withMessages([
                'data.email' => 'This account was created using social login. Please login with Google.',
            ]);
        }

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        // Check if the user's role matches the current panel
        $currentPanel = Filament::getCurrentPanel()->getId();
        $userRole = $user->role; // Assuming your user model has a 'role' attribute

        $allowedAccess = match ($currentPanel) {
            'admin' => $userRole === 'admin',
            'pbo' => $userRole === 'pbo',
            'client' => $userRole === 'client',
            default => false,
        };

        if (!$allowedAccess) {
            Filament::auth()->logout();

            throw ValidationException::withMessages([
                'data.email' => "You don't have permission to access this panel. Please use the appropriate login page for your role.",
            ]);
        }

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function mount(): void
    {
        parent::mount();

        // Get the current panel ID
        $currentPanel = Filament::getCurrentPanel()->getId();

        // Set default credentials based on the panel
        $defaultCredentials = match ($currentPanel) {
            'admin' => [
                'email' => 'estate.admin@pwan.com',
                'password' => 'password',
            ],
            'agent' => [
                'email' => 'agent@example.com',
                'password' => 'password',
            ],
            'client' => [
                'email' => 'client@example.com',
                'password' => 'password',
            ],
            default => [
                'email' => '',
                'password' => '',
            ],
        };

        // $this->form->fill([
        //     'email' => $defaultCredentials['email'],
        //     'password' => $defaultCredentials['password'],
        //     'remember' => true,
        // ]);
    }
    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
}
