<?php
namespace App\Filament\Pbo\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BankDetails extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'My Account';
    protected static string $view = 'filament.pbo.pages.bank-details';
    protected static ?string $navigationLabel = 'Payment Details';
    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'bank_name' => $user->bank_name,
            'bank_account_number' => $user->bank_account_number,
            'bank_account_name' => $user->bank_account_name,
            'bank_branch' => $user->bank_branch,
            'bank_swift_code' => $user->bank_swift_code,
            'preferred_payment_method' => $user->preferred_payment_method,
            'payment_notes' => $user->payment_notes,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bank Details')
                    ->description('Update your bank details to receive commission payments')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->placeholder('Enter your bank name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->placeholder('Enter your account number')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('bank_account_name')
                            ->label('Account Name')
                            ->placeholder('Enter the name on your account')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_branch')
                            ->label('Bank Branch')
                            ->placeholder('Enter your bank branch (if applicable)')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_swift_code')
                            ->label('SWIFT/BIC Code')
                            ->placeholder('Enter SWIFT or BIC code (if applicable)')
                            ->maxLength(50),



                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Payment Notes')
                            ->placeholder('Any additional payment information we should know about')
                            ->rows(3),
                    ])
                    ->columns(2)
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();
        $user->update($data);

        Notification::make()
            ->title('Bank details updated successfully')
            ->success()
            ->send();
    }
}