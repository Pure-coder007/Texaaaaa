<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\MyInstallmentResource\Pages;
use App\Models\PaymentPlan;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Services\DocumentGenerationService;
use App\Settings\SystemSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class MyInstallmentResource extends Resource
{
    protected static ?string $model = PaymentPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'My Installments';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Installment Plan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {
        $systemSettings = app(SystemSettings::class);

        // Get available payment methods based on system settings
        $paymentMethodOptions = [];

        if ($systemSettings->enable_bank_transfer) {
            $paymentMethodOptions['bank_transfer'] = 'Bank Transfer';
        }

        if ($systemSettings->enable_cash_payment) {
            $paymentMethodOptions['cash'] = 'Cash Payment';
        }

        // Default payment method - Select first available method
        $defaultPaymentMethod = array_key_first($paymentMethodOptions) ?: 'bank_transfer';

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchase.estate.name')
                    ->label('Estate')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_months')
                    ->label('Plan Duration')
                    ->formatStateUsing(fn (int $state): string => "{$state} Months"),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('NGN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('totalPaid')
                    ->label('Amount Paid')
                    ->money('NGN')
                    ->getStateUsing(fn (PaymentPlan $record): float => $record->totalPaid()),

                Tables\Columns\TextColumn::make('remainingBalance')
                    ->label('Remaining')
                    ->money('NGN')
                    ->getStateUsing(fn (PaymentPlan $record): float => $record->remainingBalance()),

                Tables\Columns\TextColumn::make('payment_progress')
                    ->label('Progress')
                    ->getStateUsing(function (PaymentPlan $record): string {
                        $percentage = min(100, ($record->totalPaid() / $record->total_amount) * 100);
                        return number_format($percentage, 2) . '%';
                    }),

                Tables\Columns\TextColumn::make('final_due_date')
                    ->label('Due Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->color(fn (PaymentPlan $record): string =>
                        now()->greaterThan($record->final_due_date) && $record->status !== 'completed'
                            ? 'danger'
                            : 'primary'
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'primary',
                        'completed' => 'success',
                        'defaulted' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('duration_months')
                    ->label('Duration')
                    ->options([
                        '6' => '6 Months',
                        '12' => '12 Months',
                    ]),

                Tables\Filters\Filter::make('final_due_date')
                    ->form([
                        Forms\Components\DatePicker::make('due_date_from'),
                        Forms\Components\DatePicker::make('due_date_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('final_due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('final_due_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Add the Make Payment action directly in the table
                Action::make('make_payment')
                    ->label('Make Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->modalHeading(fn (PaymentPlan $record) => 'Make Payment for ' . $record->purchase->estate->name)
                    ->modalDescription(fn (PaymentPlan $record) => "Remaining balance: ₦" . number_format($record->remainingBalance(), 2))
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Payment Amount (₦)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (PaymentPlan $record) => $record->remainingBalance())
                            ->placeholder('Enter amount to pay')
                            ->helperText('Minimum payment: ₦10,000'),

                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options($paymentMethodOptions) // Use dynamic options based on settings
                            ->required()
                            ->reactive()
                            ->default($defaultPaymentMethod)
                            ->hintAction(
                                Forms\Components\Actions\Action::make('no_payment_methods')
                                    ->label('No payment methods available')
                                    ->color('danger')
                                    ->visible(count($paymentMethodOptions) === 0)
                            ),

                            Forms\Components\Select::make('bank_account_id')
    ->label('Select Bank Account')
    ->options(function () use ($systemSettings) {
        $options = [];
        foreach ($systemSettings->getBankAccounts() as $account) {
            $options[$account['id']] = $account['bank_name'] . ' - ' . $account['account_number'];
        }
        return $options;
    })
    ->visible(fn (callable $get) => $get('payment_method') === 'bank_transfer')
    ->required()
    ->live(),

// Bank details display
Forms\Components\View::make('filament.forms.components.bank-transfer-details')
    ->visible(fn (callable $get) =>
        $get('payment_method') === 'bank_transfer' && $get('bank_account_id')
    ),

                        // Show a warning message if no payment methods are available
                        Forms\Components\Placeholder::make('no_payment_methods_warning')
                            ->label('No Payment Methods Available')
                            ->content('Please contact the administrator to enable payment methods.')
                            ->visible(fn () => count($paymentMethodOptions) === 0),

                        Forms\Components\FileUpload::make('payment_proof')
                            ->label('Upload Payment Proof')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(5120) // 5MB max
                            ->disk('public')
                            ->visible(fn (callable $get) => $get('payment_method') === 'bank_transfer')
                            ->helperText('Maximum file size: 5MB. Accepted formats: PDF, JPG, PNG'),

                        Forms\Components\TextInput::make('transaction_reference')
                            ->label('Transaction Reference')
                            ->required()
                            ->maxLength(255)
                            ->default(fn() => 'TX-' . strtoupper(Str::random(10)))
                            ->placeholder('Enter bank transaction reference')
                            ->visible(fn (callable $get) => $get('payment_method') === 'bank_transfer'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Additional Notes')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Any additional information about this payment'),
                    ])
                    ->action(function (PaymentPlan $record, array $data): void {
                        // Exit early if no payment methods available
                        if (empty($data['payment_method'])) {
                            Notification::make()
                                ->title('Payment failed')
                                ->body('No payment methods are currently available.')
                                ->danger()
                                ->send();
                            return;
                        }

                        DB::beginTransaction();

                        try {
                            // Create new payment
                            $payment = new Payment();
                            $payment->purchase_id = $record->purchase_id;
                            $payment->client_id = auth()->id();
                            $payment->payment_plan_id = $record->id;
                            $payment->payment_type = 'installment';
                            $payment->amount = $data['amount'];
                            $payment->transaction_id = $data['transaction_reference'] ?? ('TX-' . strtoupper(Str::random(10)));
                            $payment->payment_method = $data['payment_method'];
                            $payment->status = 'verified';
                            // Store bank account information if bank transfer
                            if ($data['payment_method'] === 'bank_transfer' && !empty($data['bank_account_id'])) {
                                $systemSettings = app(SystemSettings::class);
                                $bankAccount = $systemSettings->getBankAccountById((int) $data['bank_account_id']);

                                $payment->payment_details = [
                                    'notes' => $data['notes'] ?? null,
                                    'bank_id' => $data['bank_account_id'],
                                    'bank_name' => $bankAccount['bank_name'] ?? null,
                                    'account_number' => $bankAccount['account_number'] ?? null,
                                    'account_name' => $bankAccount['account_name'] ?? null,
                                ];
                            } else {
                                $payment->payment_details = [
                                    'notes' => $data['notes'] ?? null,
                                ];
                            }
                            $payment->save();

                            // Create payment proof if method is bank transfer
                            if ($data['payment_method'] === 'bank_transfer') {
                                $paymentProof = new PaymentProof();
                                $paymentProof->payment_id = $payment->id;
                                $paymentProof->transaction_reference = $data['transaction_reference'] ?? null;
                                $paymentProof->notes = $data['notes'] ?? null;
                                $paymentProof->status = 'pending';
                                $paymentProof->save();

                                // Handle file upload
                                if (!empty($data['payment_proof'])) {
                                    // Get the uploaded file path and add it to the media collection
                                    $filePath = $data['payment_proof'];
                                    $paymentProof->addMediaFromDisk($filePath, 'public')
                                        ->toMediaCollection('proof_documents');
                                }
                            }

                            // Generate installment receipt document for this specific payment
                            $documentService = app(DocumentGenerationService::class);
                            $documentService->generateInstallmentReceipt($record->purchase, $payment);

                            // Check if payment completes the plan
                            // $totalPaid = $record->totalPaid() + $data['amount'];

                            //$totalPaid = $record->purchase->payments()->where('status', 'verified')->sum('amount');

                            if ($record->totalPaid() >= $record->total_amount) {
                                // Update payment plan status
                                $record->status = 'completed';
                                $record->save();

                                // Update purchase status if it was pending
                                $purchase = $record->purchase;
                                if ($purchase->status === 'pending') {
                                    $purchase->status = 'completed';
                                    $purchase->save();
                                }

                                // Generate sales agreement and allocation letter
                                $documentService->generateSalesAgreement($purchase);
                                $documentService->generateAllocationLetter($purchase);

                                // Update plot statuses to sold
                                $purchase->purchasePlots->each(function ($purchasePlot) {
                                    $plot = $purchasePlot->plot;
                                    if ($plot && $plot->status === 'reserved') {
                                        $plot->status = 'sold';
                                        $plot->save();
                                    }
                                });
                            }

                            DB::commit();

                            Notification::make()
                                ->title('Payment submitted successfully')
                                ->body('Your payment has been submitted ')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Payment error: ' . $e->getMessage());

                            Notification::make()
                                ->title('Payment failed')
                                ->body('An error occurred while processing your payment: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn(PaymentPlan $record): bool =>
                        $record->status === 'active' && $record->remainingBalance() > 0
                    )
                    ->disabled(fn(): bool =>
                        // Disable the button if no payment methods are available
                        !$systemSettings->enable_bank_transfer && !$systemSettings->enable_cash_payment
                    ),
            ])
            ->bulkActions([
                // No bulk actions needed for installments
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyInstallments::route('/'),
            'view' => Pages\ViewInstallment::route('/{record}'),
        ];
    }
}
