<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;

class ClientRegister extends BaseRegister
{
    protected static string $view = 'filament.pages.register';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class),

                TextInput::make('phone')
                    ->label('Phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->rule(Password::default())
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->same('passwordConfirmation')
                    ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute')),

                TextInput::make('passwordConfirmation')
                    ->label('Password Confirmation')
                    ->password()
                    ->required()
                    ->dehydrated(false),

                Checkbox::make('terms')
                    ->label(new HtmlString('I agree to the <a href="/terms" class="text-primary-500 hover:underline" target="_blank">terms of service</a> and  <a href="/privacy" class="text-primary-500 hover:underline" target="_blank">privacy policy</a>'))
                    ->required(),
            ]);
    }

    public function register(): ?RegistrationResponse
    {
        $data = $this->form->getState();

        // Remove terms and password confirmation from the data
        $data = collect($data)
            ->except(['terms', 'passwordConfirmation'])
            ->toArray();

        // Set role as client and set onboarding_completed to false
        $data['role'] = 'client';
        $data['onboarding_completed'] = false;
        $data['status'] = 'active';

        $user = User::create($data);


        Filament::auth()->login($user);

        // Return a custom response that will redirect to the onboarding page
        return app(RegistrationResponse::class);
    }
}
