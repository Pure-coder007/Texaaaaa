<?php

namespace App\Filament\Client\Resources\MyInstallmentResource\Pages;

use App\Filament\Client\Resources\MyInstallmentResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Models\PaymentPlan;
use App\Models\Payment;
use App\Models\PaymentProof;
use Filament\Actions\Action;
use Livewire\WithFileUploads;
use Filament\Forms;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\DocumentGenerationService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Settings\SystemSettings;


class ViewInstallment extends ViewRecord
{
    use WithFileUploads;

    protected static string $resource = MyInstallmentResource::class;

    protected SystemSettings $systemSettings;

    // Initialize the system settings in the constructor
    public function boot()
    {
        $this->systemSettings = app(SystemSettings::class);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Payment Plan Details')
                    ->schema([
                        Infolists\Components\Grid::make()
                            ->schema([
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('purchase.estate.name')
                                        ->label('Estate')
                                        ->icon('heroicon-o-home'),
                                    Infolists\Components\TextEntry::make('purchase.payment_plan_type')
                                        ->label('Payment Plan')
                                        ->formatStateUsing(fn (string $state): string => match($state) {
                                            'outright' => 'Outright Payment',
                                            '6_months' => '6 Months Installment',
                                            '12_months' => '12 Months Installment',
                                            default => $state,
                                        })
                                        ->badge()
                                        ->color(fn (string $state): string => match($state) {
                                            'outright' => 'success',
                                            '6_months' => 'info',
                                            '12_months' => 'primary',
                                            default => 'gray',
                                        })
                                        ->icon('heroicon-o-calendar'),
                                ]),
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('duration_months')
                                        ->label('Duration')
                                        ->formatStateUsing(fn (int $state): string => "{$state} Months")
                                        ->icon('heroicon-o-clock'),
                                    Infolists\Components\TextEntry::make('final_due_date')
                                        ->label('Due Date')
                                        ->date('M d, Y')
                                        ->color(fn (PaymentPlan $record): string =>
                                            now()->greaterThan($record->final_due_date) && $record->status !== 'completed'
                                                ? 'danger'
                                                : 'primary'
                                        )
                                        ->icon('heroicon-o-calendar'),
                                ]),
                            ])
                            ->columns(2),

                        Infolists\Components\Grid::make()
                            ->schema([
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('total_amount')
                                        ->label('Total Amount')
                                        ->money('NGN')
                                        ->icon('heroicon-o-currency-dollar'),
                                    Infolists\Components\TextEntry::make('initial_payment')
                                        ->label('Initial Payment')
                                        ->money('NGN')
                                        ->icon('heroicon-o-banknotes'),
                                ]),
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('totalPaid')
                                        ->state(fn (PaymentPlan $record): float => $record->totalPaid())
                                        ->label('Amount Paid')
                                        ->money('NGN')
                                        ->icon('heroicon-o-check-circle')
                                        ->color('success'),
                                    Infolists\Components\TextEntry::make('remainingBalance')
                                        ->state(fn (PaymentPlan $record): float => $record->remainingBalance())
                                        ->label('Remaining Balance')
                                        ->money('NGN')
                                        ->icon('heroicon-o-exclamation-circle')
                                        ->color(fn (PaymentPlan $record): string =>
                                            $record->remainingBalance() > 0 ? 'warning' : 'success'
                                        ),
                                ]),
                            ])
                            ->columns(2),

                        Infolists\Components\Grid::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('premium_percentage')
                                    ->label('Premium Percentage')
                                    ->formatStateUsing(fn (string $state): string => "{$state}%")
                                    ->icon('heroicon-o-plus-circle'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active' => 'primary',
                                        'completed' => 'success',
                                        'defaulted' => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon(fn (string $state): string => match ($state) {
                                        'active' => 'heroicon-o-play',
                                        'completed' => 'heroicon-o-check-circle',
                                        'defaulted' => 'heroicon-o-x-circle',
                                        default => 'heroicon-o-question-mark-circle',
                                    }),
                                Infolists\Components\TextEntry::make('payment_progress')
                                    ->state(function (PaymentPlan $record): string {
                                        $percentage = min(100, ($record->totalPaid() / $record->total_amount) * 100);
                                        return number_format($percentage, 2) . '%';
                                    })
                                    ->label('Payment Progress'),
                                Infolists\Components\TextEntry::make('payment_progress_bar')
                                    ->state(function (PaymentPlan $record): float {
                                        return ($record->totalPaid() / $record->total_amount) * 100;
                                    })
                                    ->label('Progress')
                                    ->color(fn (PaymentPlan $record): string =>
                                        $record->totalPaid() >= $record->total_amount ? 'success' : 'primary'
                                    ),
                            ])
                            ->columns(2),
                    ]),

                Infolists\Components\Section::make('Payment History')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('payments')
                            ->schema([
                                Infolists\Components\Grid::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('amount')
                                            ->label('Amount')
                                            ->money('NGN'),
                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label('Date')
                                            ->date('M d, Y'),
                                        Infolists\Components\TextEntry::make('payment_method')
                                            ->label('Method')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'bank_transfer' => 'primary',
                                                'cash' => 'success',
                                                'credit_card' => 'info',
                                                default => 'gray',
                                            }),
                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'pending' => 'warning',
                                                'verified' => 'success',
                                                'failed' => 'danger',
                                                default => 'gray',
                                            }),
                                    ])
                                    ->columns(4),
                            ]),
                    ]),

                Infolists\Components\Section::make('Estate Information')
                    ->schema([
                        Infolists\Components\Grid::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('purchase.estate.name')
                                    ->label('Estate Name'),
                                Infolists\Components\TextEntry::make('purchase.estate.city.name')
                                    ->label('City'),
                                Infolists\Components\TextEntry::make('purchase.estate.location.name')
                                    ->label('Location'),
                                Infolists\Components\TextEntry::make('purchase.total_plots')
                                    ->label('Total Plots'),
                                Infolists\Components\TextEntry::make('purchase.total_area')
                                    ->label('Total Area (sqm)'),
                            ])
                            ->columns(3),
                    ])
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        // Get available payment methods based on system settings
        $paymentMethodOptions = [];

        if ($this->systemSettings->enable_bank_transfer) {
            $paymentMethodOptions['bank_transfer'] = 'Bank Transfer';
        }

        if ($this->systemSettings->enable_cash_payment) {
            $paymentMethodOptions['cash'] = 'Cash Payment';
        }

        // Default payment method - Select first available method
        $defaultPaymentMethod = array_key_first($paymentMethodOptions) ?: 'bank_transfer';

        return [

            Action::make('make_payment')
                ->label('Make Payment')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->modalHeading('Make Installment Payment')
                ->modalDescription(fn () => "Remaining balance: ₦" . number_format($this->record->remainingBalance(), 2))
                ->form([
                    Forms\Components\TextInput::make('amount')
                        ->label('Payment Amount (₦)')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn () => $this->record->remainingBalance())
                        ->placeholder('Enter amount to pay')
                        ->helperText('Minimum payment: ₦10,000'),

                    Forms\Components\Select::make('payment_method')
                        ->label('Payment Method')
                        ->options($paymentMethodOptions)
                        ->required()
                        ->reactive()
                        ->default($defaultPaymentMethod),

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
                ->action(function (array $data): void {
                    DB::beginTransaction();

                    try {
                        // Create new payment
                        $payment = new Payment();
                        $payment->purchase_id = $this->record->purchase_id;
                        $payment->client_id = auth()->id();
                        $payment->payment_plan_id = $this->record->id;
                        $payment->payment_type = 'installment';
                        $payment->amount = $data['amount'];
                        $payment->transaction_id = $data['transaction_reference'] ?? ('TX-' . strtoupper(Str::random(10)));
                        $payment->payment_method = $data['payment_method'];
                        $payment->status = 'verified'; // Always start as pending for verification
                        $payment->payment_details = [
                            'notes' => $data['notes'] ?? null,
                        ];
                        $payment->save();

                        // Create payment proof if method is bank transfer
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

                        // Generate installment receipt document for this specific payment
                        $documentService = app(DocumentGenerationService::class);
                        $documentService->generateInstallmentReceipt($this->record->purchase, $payment);

                        // Check if payment completes the plan
                        $totalPaid = $this->record->totalPaid() + $data['amount'];
                        if ($totalPaid >= $this->record->total_amount) {
                            // Update payment plan status
                            $this->record->status = 'completed';
                            $this->record->save();

                            // Update purchase status if it was pending
                            $purchase = $this->record->purchase;
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
                            ->body('Your payment has been submitted')
                            ->success()
                            ->send();

                        // Refresh the page to show the new payment
                        $this->redirect(route('filament.client.resources.my-installments.view', ['record' => $this->record]));

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
                ->visible(false),

            Action::make('download_all_documents')
                ->label('Download Documents')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    // This will be handled by the frontend to redirect
                    $this->redirect(route('filament.client.resources.my-documents.documents', [
                        'folder' => $this->record->purchase->clientFolder->id ?? null
                    ]));
                })
                ->visible(fn(): bool =>
                    $this->record->purchase->clientFolder !== null
                ),
        ];
    }
}