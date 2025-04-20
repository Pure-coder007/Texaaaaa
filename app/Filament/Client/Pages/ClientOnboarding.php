<?php

namespace App\Filament\Client\Pages;

use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ClientOnboarding extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.client.pages.client-onboarding';
    protected static ?string $title = 'Complete Your Profile';
    protected static ?string $navigationLabel = 'Complete Your Profile';
    protected ?string $heading = 'Complete Your Profile';
    public ?array $data = [];

    protected static bool $shouldRegisterNavigation = false;



    // Redirect away if onboarding is already completed
    public function mount(): void
    {
        if (auth()->user()->onboarding_completed) {
            redirect()->route('filament.client.pages.dashboard');
        }

        $this->form->fill(auth()->user()->only([
            'spouse_name',
            'date_of_birth',
            'gender',
            'marital_status',
            'nationality',
            'languages_spoken',
            'address',
            'country_of_residence',
            'mobile_number',
            'occupation',
            'employer_name',
            'next_of_kin_name',
            'next_of_kin_relationship',
            'next_of_kin_address',
            'next_of_kin_phone',
            'next_of_kin_email',
            'terms_accepted',
        ]));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Personal Information')
                        ->icon('heroicon-o-user')
                        ->description('Tell us about yourself')
                        ->schema([
                            TextInput::make('spouse_name')
                                ->label('Name of Spouse')
                                ->required()
                                ->maxLength(255),

                            DatePicker::make('date_of_birth')
                                ->label('Date of Birth')
                                ->required()
                                ->maxDate(now()->subYears(18)),

                            Select::make('gender')
                                ->label('Gender')
                                ->required()
                                ->options([
                                    'male' => 'Male',
                                    'female' => 'Female',
                                ]),

                            Select::make('marital_status')
                                ->label('Marital Status')
                                ->required()
                                ->options([
                                    'single' => 'Single',
                                    'married' => 'Married',
                                    'divorced' => 'Divorced',
                                    'widowed' => 'Widowed',
                                ]),

                            TextInput::make('nationality')
                                ->label('Nationality')
                                ->required()
                                ->maxLength(100),

                            Select::make('languages_spoken')
                                ->label('Language(s) Spoken')
                                ->multiple()
                                ->required()
                                ->options([
                                    'english' => 'English',
                                    'yoruba' => 'Yoruba',
                                    'igbo' => 'Igbo',
                                    'hausa' => 'Hausa',
                                    'french' => 'French',
                                    'spanish' => 'Spanish',
                                    'other' => 'Other',
                                ]),
                        ]),

                    Step::make('Contact Information')
                        ->icon('heroicon-o-map-pin')
                        ->description('How we can reach you')
                        ->schema([
                            Textarea::make('address')
                                ->label('Address')
                                ->required()
                                ->rows(3)
                                ->columnSpanFull(),

                            Select::make('country_of_residence')
                                ->label('Country of Residence')
                                ->required()
                                ->options([
                                    'nigeria' => 'Nigeria',
                                    'ghana' => 'Ghana',
                                    'kenya' => 'Kenya',
                                    'south_africa' => 'South Africa',
                                    'united_kingdom' => 'United Kingdom',
                                    'united_states' => 'United States',
                                    'canada' => 'Canada',
                                    'other' => 'Other',
                                ]),

                            TextInput::make('mobile_number')
                                ->label('Mobile Number')
                                ->required()
                                ->tel()
                                ->maxLength(20),
                        ]),

                    Step::make('Employment Details')
                        ->icon('heroicon-o-briefcase')
                        ->description('Your work information')
                        ->schema([
                            TextInput::make('occupation')
                                ->label('Occupation')
                                ->required()
                                ->maxLength(100),

                            TextInput::make('employer_name')
                                ->label('Employer\'s Name')
                                ->required()
                                ->maxLength(100),
                        ]),

                    Step::make('Next of Kin Details')
                        ->icon('heroicon-o-user-group')
                        ->description('Emergency contact information')
                        ->schema([
                            TextInput::make('next_of_kin_name')
                                ->label('Next of Kin Name')
                                ->required()
                                ->maxLength(255),

                            Select::make('next_of_kin_relationship')
                                ->label('Next of Kin Relationship')
                                ->required()
                                ->options([
                                    'parent' => 'Parent',
                                    'spouse' => 'Spouse',
                                    'sibling' => 'Sibling',
                                    'child' => 'Child',
                                    'other' => 'Other',
                                ]),

                            Textarea::make('next_of_kin_address')
                                ->label('Next of Kin Address')
                                ->required()
                                ->rows(3)
                                ->columnSpanFull(),

                            TextInput::make('next_of_kin_phone')
                                ->label('Next of Kin Phone Number')
                                ->required()
                                ->tel()
                                ->maxLength(20),

                            TextInput::make('next_of_kin_email')
                                ->label('Next of Kin Email Address')
                                ->email()
                                ->maxLength(255),
                        ]),

                    Step::make('Terms & Submission')
                        ->icon('heroicon-o-document-text')
                        ->description('Review and accept terms')
                        ->schema([
                            Checkbox::make('terms_accepted')
                                ->label(new HtmlString('I agree to the terms of service and privacy policy'))
                                ->required()
                                ->accepted(),
                        ]),
                ])
                ->persistStepInQueryString()
                ->submitAction(view('livewire.partials.wizard-submit-button')),
            ])
            ->statePath('data')
            ->model(auth()->user());
    }

    public function submitForm()
    {
        // Validate and save the form data
        $data = $this->form->getState();

        // Update the user
        auth()->user()->update([
            ...$data,
            'onboarding_completed' => true,
            'submission_date' => now(),
        ]);

        // Show success notification
        Notification::make()
            ->title('Profile completed')
            ->success()
            ->send();

        // Redirect to dashboard
        redirect()->route('filament.client.pages.dashboard');
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getMaxContentWidth(): ?string
    {
        return '5xl';
    }
}
