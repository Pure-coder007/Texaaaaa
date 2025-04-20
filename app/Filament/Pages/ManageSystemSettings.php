<?php

namespace App\Filament\Pages;

use App\Settings\SystemSettings;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class ManageSystemSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static string $settings = SystemSettings::class;

    protected static ?string $title = 'System Settings';

    protected static ?string $slug = 'settings/system';

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Tabs::make('settings_tabs')
                    ->tabs([
                        Tabs\Tab::make('General')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Section::make('Site Information')
                                    ->schema([
                                        TextInput::make('site_name')
                                            ->label('Site Name')
                                            ->required()
                                            ->maxLength(100),

                                        TextInput::make('company_name')
                                            ->label('Company Name')
                                            ->required()
                                            ->maxLength(100),

                                        TextInput::make('website')
                                            ->label('Website URL')
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Section::make('Header Information')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('header_phone')
                                                    ->label('Header Phone Number')
                                                    ->required(),

                                                TextInput::make('header_email')
                                                    ->label('Header Email')
                                                    ->email()
                                                    ->required(),
                                            ]),

                                        Toggle::make('show_register_button')
                                            ->label('Show Register Button')
                                            ->helperText('Whether to show the register button in the header for non-logged in users')
                                            ->default(true),
                                    ]),
                            ]),

                        Tabs\Tab::make('Payment')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Section::make('Payment Methods')
                                    ->schema([
                                        Toggle::make('enable_bank_transfer')
                                            ->label('Enable Bank Transfer')
                                            ->helperText('Allow payment via bank transfer')
                                            ->onColor('success'),

                                        Toggle::make('enable_cash_payment')
                                            ->label('Enable Cash Payment')
                                            ->helperText('Allow payment via cash at office')
                                            ->onColor('success'),
                                    ]),

                                Section::make('Installment Payment Settings')
                                    ->schema([
                                        TextInput::make('installment_initial_payment_percentage')
                                            ->label('Initial Payment Percentage')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(20)
                                            ->required()
                                            ->helperText('Minimum percentage required as initial payment for installment plans'),

                                        TextInput::make('installment_default_penalty_percentage')
                                            ->label('Default Penalty Percentage')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(10)
                                            ->required()
                                            ->helperText('Penalty percentage applied if full payment is not completed by the end of the timeframe'),
                                    ]),

                                Section::make('Bank Transfer Settings')
                                    ->schema([
                                        Textarea::make('bank_transfer_instructions')
                                            ->label('Bank Transfer Instructions')
                                            ->helperText('Instructions shown to customers who choose bank transfer')
                                            ->rows(3)
                                            ->required(),

                                        Fieldset::make('Bank Account 1')
                                            ->schema([
                                                TextInput::make('bank_name_1')
                                                    ->label('Bank Name')
                                                    ->required(),

                                                TextInput::make('bank_account_number_1')
                                                    ->label('Account Number')
                                                    ->required(),

                                                TextInput::make('bank_account_name_1')
                                                    ->label('Account Name')
                                                    ->required(),
                                            ]),

                                        Fieldset::make('Bank Account 2')
                                            ->schema([
                                                TextInput::make('bank_name_2')
                                                    ->label('Bank Name')
                                                    ->required(),

                                                TextInput::make('bank_account_number_2')
                                                    ->label('Account Number')
                                                    ->required(),

                                                TextInput::make('bank_account_name_2')
                                                    ->label('Account Name')
                                                    ->required(),
                                            ]),

                                        Fieldset::make('Bank Account 3')
                                            ->schema([
                                                TextInput::make('bank_name_3')
                                                    ->label('Bank Name')
                                                    ->required(),

                                                TextInput::make('bank_account_number_3')
                                                    ->label('Account Number')
                                                    ->required(),

                                                TextInput::make('bank_account_name_3')
                                                    ->label('Account Name')
                                                    ->required(),
                                            ]),
                                    ]),

                                Section::make('Cash Payment Settings')
                                    ->schema([
                                        Textarea::make('cash_payment_instructions')
                                            ->label('Cash Payment Instructions')
                                            ->helperText('Instructions shown to customers who choose cash payment')
                                            ->rows(3)
                                            ->required(),

                                        Textarea::make('cash_payment_office_address')
                                            ->label('Office Address')
                                            ->helperText('Address where customers can make cash payments')
                                            ->rows(2)
                                            ->required(),

                                        TextInput::make('cash_payment_office_hours')
                                            ->label('Office Hours')
                                            ->helperText('E.g., Monday to Friday, 9:00 AM - 5:00 PM')
                                            ->required(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Fees & Taxes')
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                Section::make('Tax Settings')
                                    ->schema([
                                        Toggle::make('enable_tax')
                                            ->label('Enable Tax')
                                            ->helperText('Apply tax to property purchases')
                                            ->reactive(),

                                        TextInput::make('tax_name')
                                            ->label('Tax Name')
                                            ->default('VAT')
                                            ->required()
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('enable_tax')),

                                        TextInput::make('tax_percentage')
                                            ->label('Tax Percentage')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(7.5)
                                            ->required()
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('enable_tax')),

                                        Toggle::make('tax_display')
                                            ->label('Display Tax on Frontend')
                                            ->helperText('When enabled, tax will be displayed separately on the purchase page')
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('enable_tax')),
                                    ]),

                                Section::make('Processing Fee Settings')
                                    ->schema([
                                        Toggle::make('enable_processing_fee')
                                            ->label('Enable Processing Fee')
                                            ->helperText('Apply a processing fee to property purchases')
                                            ->reactive(),

                                        TextInput::make('processing_fee_name')
                                            ->label('Fee Name')
                                            ->default('Platform Fee')
                                            ->required()
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('enable_processing_fee')),

                                        Select::make('processing_fee_type')
                                            ->label('Fee Type')
                                            ->options([
                                                'percentage' => 'Percentage',
                                                'fixed' => 'Fixed Amount',
                                            ])
                                            ->required()
                                            ->reactive()
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('enable_processing_fee')),

                                        TextInput::make('processing_fee_value')
                                            ->label(fn (\Filament\Forms\Get $get) =>
                                                $get('processing_fee_type') === 'percentage' ? 'Fee Percentage' : 'Fee Amount'
                                            )
                                            ->numeric()
                                            ->suffix(fn (\Filament\Forms\Get $get) =>
                                                $get('processing_fee_type') === 'percentage' ? '%' : ''
                                            )
                                            ->required()
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('enable_processing_fee')),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('min_processing_fee')
                                                    ->label('Minimum Fee')
                                                    ->helperText('Minimum fee amount (for percentage-based fees)')
                                                    ->numeric()
                                                    ->required()
                                                    ->visible(fn (\Filament\Forms\Get $get) =>
                                                        $get('enable_processing_fee') && $get('processing_fee_type') === 'percentage'
                                                    ),

                                                TextInput::make('max_processing_fee')
                                                    ->label('Maximum Fee')
                                                    ->helperText('Maximum fee amount (for percentage-based fees)')
                                                    ->numeric()
                                                    ->required()
                                                    ->visible(fn (\Filament\Forms\Get $get) =>
                                                        $get('enable_processing_fee') && $get('processing_fee_type') === 'percentage'
                                                    ),
                                            ]),

                                        Toggle::make('processing_fee_display')
                                            ->label('Display Fee on Frontend')
                                            ->helperText('When enabled, processing fee will be displayed separately on the purchase page')
                                            ->visible(fn (\Filament\Forms\Get $get) => $get('enable_processing_fee')),
                                    ]),
                            ]),

                            Tabs\Tab::make('Documents')
    ->icon('heroicon-o-document-text')
    ->schema([
        Section::make('Company Information For Documents')
            ->schema([
                TextInput::make('document_company_name')
                    ->label('Company Name')
                    ->default('PWAN CHAMPION REALTORS AND ESTATE LIMITED')
                    ->required(),

                Textarea::make('document_company_address')
                    ->label('Company Address')
                    ->default('No 10B Muritala Eletu Street Beside Mayhill Hotel, Jakande Bus Stop Osapa London Lekki Phase 2 Lagos.')
                    ->rows(3)
                    ->required(),

                Grid::make(2)
                    ->schema([
                        TextInput::make('document_company_phone')
                            ->label('Phone Number')
                            ->default('09076126725')
                            ->required(),

                        TextInput::make('document_company_whatsapp')
                            ->label('WhatsApp Number')
                            ->default('09076126725')
                            ->required(),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('document_company_email')
                            ->label('Email Address')
                            ->default('pwanchampion@gmail.com')
                            ->email()
                            ->required(),

                        TextInput::make('document_company_website')
                            ->label('Website')
                            ->default('www.pwanchampion.com')
                            ->required(),
                    ]),

                TextInput::make('document_company_slogan')
                    ->label('Company Slogan')
                    ->default('...land is wealth')
                    ->required(),
            ]),

        Section::make('Receipt Settings')
            ->schema([
                Fieldset::make('Receipt Headers')
                    ->schema([
                        TextInput::make('receipt_title')
                            ->label('Receipt Title')
                            ->default('Sales Receipt')
                            ->required(),

                        TextInput::make('receipt_file_prefix')
                            ->label('File Number Prefix')
                            ->default('PWC/ECE/')
                            ->required(),
                    ]),

                Fieldset::make('Signature Section')
                    ->schema([
                        TextInput::make('receipt_signatory_name')
                            ->label('Signatory Name')
                            ->default('AMB. DR. BENEDICT ABUDU IBHADON')
                            ->required(),

                        TextInput::make('receipt_signatory_title')
                            ->label('Signatory Title')
                            ->default('Managing Director')
                            ->required(),

                        TextInput::make('receipt_company_name_short')
                            ->label('Company Name (Short)')
                            ->default('PWAN CHAMPION')
                            ->required(),

                        TextInput::make('receipt_company_description')
                            ->label('Company Description')
                            ->default('REALTORS AND ESTATE LIMITED')
                            ->required(),
                    ]),
            ]),

        Section::make('Allocation Letter Settings')
            ->schema([
                TextInput::make('allocation_letter_title')
                    ->label('Allocation Letter Title')
                    ->default('PHYSICAL ALLOCATION NOTIFICATION')
                    ->required(),

                TextInput::make('allocation_note')
                    ->label('Allocation Note')
                    ->default('This letter is temporary pending the receipt of your survey plan.')
                    ->required(),

                Textarea::make('allocation_footer_text')
                    ->label('Footer Text')
                    ->default('Subsequently, you are responsible for the clearing of your land after allocation.')
                    ->rows(2),
            ]),

        Section::make('Contract Settings')
            ->schema([
                TextInput::make('contract_title')
                    ->label('Contract Title')
                    ->default('CONTRACT OF SALE')
                    ->required(),

                Textarea::make('contract_prepared_by')
                    ->label('Prepared By Information')
                    ->default("EMMANUEL NDUBISI, ESQ.\nC/O THE LAW FIRM OF OLUKAYODE A. AKOMOLAFE\n2, OLUFUNMILOLA OKIKIOLU STREET,\nOFF TOYIN STREET,\nIKEJA,\nLAGOS.")
                    ->rows(5)
                    ->required(),

                Textarea::make('contract_vendor_description')
                    ->label('Vendor Description')
                    ->default('is a Limited Liability Company incorporated under the Laws of the Federal Republic of Nigeria with its office at 10B Muritala Eletu Street Beside Mayhill Hotel Jakande Bus stop, Osapa London, Lekki Pennisula Phase 2, Lagos State (hereinafter referred to as \'THE VENDOR\' which expression shall wherever the context so admits include its assigns, legal representatives and successors-in-title) of the one part.')
                    ->rows(3)
                    ->required(),
            ]),

        Section::make('Document Footer')
            ->schema([
                TextInput::make('document_footer_text')
                    ->label('Footer Text')
                    ->default('...land is wealth')
                    ->required(),
            ]),
    ]),
                    ]),
            ]);
    }

    public function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save Settings')
                ->icon('heroicon-o-check'),
        ];
    }
}