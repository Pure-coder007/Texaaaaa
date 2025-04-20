<?php

namespace App\Filament\Pages;


use App\Enums\AgentReferralStatus;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Register extends BaseRegister
{
    protected static string $view = 'filament.pages.register';

    // Add a property to store the referral code from the URL
    public $referralCode = null;

    // Initialize the component and grab the referral code from the URL if available
    public function mount(): void
    {
        parent::mount();

        // Get the referral code from the query string if present
        $this->referralCode = request()->query('ref');

        // If we have a referral code in the URL, set it in the form
        if ($this->referralCode) {
            $this->form->fill([
                'referrer_code' => $this->referralCode,
            ]);
        }
    }

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
                    ->maxLength(20),

                DatePicker::make('date_of_birth')
                    ->label('Date of Birth')
                    ->required()
                    ->maxDate(now()->subYears(18))
                    ->displayFormat('d/m/Y')
                    ->visible(fn() => Filament::getCurrentPanel()->getId() === 'pbo'),

                TextInput::make('pbo_code')
                    ->label('PBO Code (PBO portal Username)')
                    ->helperText('Enter your unique PBO portal Username if you have one')
                    ->maxLength(50)
                    ->required()
                    ->visible(fn() => Filament::getCurrentPanel()->getId() === 'pbo'),

                // Change pbo_code to referrer_code for clarity
                TextInput::make('referrer_code')
                    ->label('Referral Code')
                    ->helperText('Enter the referral code from another PBO (optional)')
                    ->maxLength(50)
                    ->visible(fn() => Filament::getCurrentPanel()->getId() === 'pbo')
                    ->dehydrated(true),

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
                    ->label('I agree to the terms of service and privacy policy')
                    ->required(),
            ]);
    }

    public function register(): ?RegistrationResponse
    {
        $data = $this->form->getState();

        // Remove terms and password confirmation from the data
        $userData = collect($data)
            ->except(['terms', 'passwordConfirmation', 'referrer_code'])
            ->toArray();

        // Extract the referrer code
        $referrerCode = $data['referrer_code'] ?? $this->referralCode;

        // Set role based on the current panel
        $panelId = Filament::getCurrentPanel()->getId();
        $userData['role'] = match ($panelId) {
            'client' => 'client',
            'pbo' => 'pbo',
            default => 'client', // Default to client for safety
        };

        // Create the user
        $user = User::create($userData);

        // Process referral if we have a referrer code and this is an agent registration
        if ($referrerCode && $userData['role'] === 'pbo') {
            $this->processReferral($referrerCode, $user);
        }

        // Fire the registered event
        // event(new Registered($user));

        // Log the user in
        Filament::auth()->login($user);

        return app(RegistrationResponse::class);
    }

    /**
     * Generate a unique agent code
     */
    protected function generateAgentCode(string $name): string
    {
        $prefix = strtoupper(substr(str_replace(' ', '', $name), 0, 2));
        $code = $prefix . rand(1000, 9999);

        // Ensure code is unique
        while (User::where('pbo_code', $code)->exists()) {
            $code = $prefix . rand(1000, 9999);
        }

        return $code;
    }

    /**
     * Process the referral after user creation
     */
    protected function processReferral(string $referrerCode, User $user): void
    {
        // Find the referring agent
        $referrer = User::where('pbo_code', $referrerCode)
            ->where('role', 'pbo')
            ->first();

        if (!$referrer) {
            return;
        }

        // Update the referred user
        $user->update([
            'referred_by' => $referrer->id,
        ]);

        // Find any pending referral record
        $referral = $referrer->agentReferrals()
            ->where('email', $user->email)
            ->where('status', 'converted')
            ->first();

        if ($referral) {
            // Update the existing referral record
            $referral->update([
                'referred_id' => $user->id,
                'status' => 'converted',
                'converted_at' => now(),
            ]);
        } else {
            // Create a new referral record if one didn't exist
            $referrer->agentReferrals()->create([
                'referred_id' => $user->id,
                'email' => $user->email,
                'status' => 'converted',
                'converted_at' => now(),
            ]);
        }

        // Award points to the referrer
        $pointsValue = 10;

        // Add points to the referrer
        $referrer->pboPoints()->create([
            'points' => $pointsValue,
            'type' => 'referral',
            'description' => "Points for referring {$user->name}",
        ]);
    }
}