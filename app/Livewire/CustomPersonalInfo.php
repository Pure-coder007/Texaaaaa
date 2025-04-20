<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class CustomPersonalInfo extends PersonalInfo
{
    // Override the fields to include all your custom fields
    public array $only = [
        'name',
        'email',
        'phone',
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
    ];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        // Override default components
                        $this->getNameComponent(),
                        $this->getEmailComponent(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telephone Number')
                            ->tel(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('spouse_name')
                            ->label('Name of Spouse'),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->maxDate(now()->subYears(18)),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                        Forms\Components\Select::make('marital_status')
                            ->options([
                                'single' => 'Single',
                                'married' => 'Married',
                                'divorced' => 'Divorced',
                                'widowed' => 'Widowed',
                            ]),
                        Forms\Components\TextInput::make('nationality'),
                        Forms\Components\CheckboxList::make('languages_spoken')
                            ->options([
                                'english' => 'English',
                                'yoruba' => 'Yoruba',
                                'hausa' => 'Hausa',
                                'igbo' => 'Igbo',
                                'french' => 'French',
                                'other' => 'Other',
                            ])
                            ->columns(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('country_of_residence'),
                        Forms\Components\TextInput::make('mobile_number')
                            ->tel(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Employment Details')
                    ->schema([
                        Forms\Components\TextInput::make('occupation'),
                        Forms\Components\TextInput::make('employer_name')
                            ->label("Employer's Name"),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Next of Kin Details')
                    ->schema([
                        Forms\Components\TextInput::make('next_of_kin_name')
                            ->label('Next of Kin Name'),
                        Forms\Components\Select::make('next_of_kin_relationship')
                            ->label('Relationship')
                            ->options([
                                'parent' => 'Parent',
                                'spouse' => 'Spouse',
                                'sibling' => 'Sibling',
                                'child' => 'Child',
                                'other' => 'Other',
                            ]),
                        Forms\Components\Textarea::make('next_of_kin_address')
                            ->label('Address')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('next_of_kin_phone')
                            ->label('Phone Number')
                            ->tel(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    // Override the name component
    protected function getNameComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('name')
            ->label('Full Name')
            ->required();
    }

    // Override the email component
    protected function getEmailComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('email')
            ->email()
            ->required();
    }

    // Override the notification
    protected function sendNotification(): void
    {
        Notification::make()
            ->success()
            ->title('Profile Updated')
            ->body('Your profile information has been successfully updated.')
            ->send();
    }
}
