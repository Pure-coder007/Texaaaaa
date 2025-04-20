<?php

namespace App\Filament\Client\Resources\PaymentResource\Pages;

use App\Filament\Client\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Payment Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_id')
                            ->label('Transaction ID'),
                        Infolists\Components\TextEntry::make('amount')
                            ->money('NGN'),
                        Infolists\Components\TextEntry::make('payment_method')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'bank_transfer' => 'primary',
                                'cash' => 'success',
                                'credit_card' => 'info',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'verified' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Payment Date')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Property Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('purchase.estate.name')
                            ->label('Estate'),
                        Infolists\Components\TextEntry::make('purchase.total_plots')
                            ->label('Total Plots'),
                        Infolists\Components\TextEntry::make('purchase.payment_plan_type')
                            ->label('Payment Plan')
                            ->formatStateUsing(fn (string $state): string => match($state) {
                                'outright' => 'Outright Payment',
                                '6_months' => '6 Months Installment',
                                '12_months' => '12 Months Installment',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('purchase_payment')
                            ->label('Payment Status')
                            ->state(function ($record) {
                                $purchase = $record->purchase;
                                $totalPaid = $purchase->totalPaid();
                                $totalAmount = $purchase->total_amount;
                                $percentage = ($totalPaid / $totalAmount) * 100;

                                return "₦" . number_format($totalPaid, 2) . " / ₦" . number_format($totalAmount, 2) . " (" . round($percentage, 1) . "%)";
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->purchase !== null),

                Infolists\Components\Section::make('Payment Proof')
                    ->schema([
                        // Infolists\Components\ImageEntry::make('paymentProof')
                        //     ->label('Proof of Payment')
                        //     ->columnSpanFull()
                        //     ->visible(fn ($record) => $record->paymentProof?->getFirstMedia('proof_documents') !== null),

                        Infolists\Components\TextEntry::make('paymentProof.transaction_reference')
                            ->label('Transaction Reference'),

                        Infolists\Components\TextEntry::make('paymentProof.notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->visible(fn ($record) => $record->paymentProof !== null),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
         Actions\Action::make('download_receipt')
                    ->label(fn (Payment $record): string =>
                        $record->payment_type === 'installment' ? 'Download Installment Receipt' : 'Download Receipt'
                    )
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(function (Payment $record): ?string {
                        // For installment payments, look for installment receipt
                        if ($record->payment_type === 'installment') {
                            $installmentReceipt = \App\Models\ClientDocument::query()
                                ->whereHas('folder', function ($query) use ($record) {
                                    $query->where('client_id', $record->client_id)
                                        ->where('purchase_id', $record->purchase_id);
                                })
                                ->where('document_type', 'installment_receipt')
                                ->where('metadata->payment_id', $record->id)
                                ->where('status', 'completed')
                                ->first();

                            if ($installmentReceipt) {
                                return $installmentReceipt->getFirstMediaUrl('document_file');
                            }
                        }

                        // For outright payments or as a fallback, look for regular receipt
                        $regularReceipt = \App\Models\ClientDocument::query()
                            ->whereHas('folder', function ($query) use ($record) {
                                $query->where('client_id', $record->client_id)
                                    ->where('purchase_id', $record->purchase_id);
                            })
                            ->where('document_type', 'receipt')
                            ->where('status', 'completed')
                            ->first();

                        return $regularReceipt?->getFirstMediaUrl('document_file');
                    })
                    ->openUrlInNewTab()
                    ->visible(function (Payment $record): bool {
                        // Only show for verified payments with available receipt
                        if ($record->status !== 'verified') {
                            return false;
                        }

                        // Check for installment receipt first if it's an installment payment
                        if ($record->payment_type === 'installment') {
                            $hasInstallmentReceipt = \App\Models\ClientDocument::query()
                                ->whereHas('folder', function ($query) use ($record) {
                                    $query->where('client_id', $record->client_id)
                                        ->where('purchase_id', $record->purchase_id);
                                })
                                ->where('document_type', 'installment_receipt')
                                ->where('metadata->payment_id', $record->id)
                                ->where('status', 'completed')
                                ->exists();

                            if ($hasInstallmentReceipt) {
                                return true;
                            }
                        }

                        // Fall back to checking for regular receipt
                        return \App\Models\ClientDocument::query()
                            ->whereHas('folder', function ($query) use ($record) {
                                $query->where('client_id', $record->client_id)
                                    ->where('purchase_id', $record->purchase_id);
                            })
                            ->where('document_type', 'receipt')
                            ->where('status', 'completed')
                            ->exists();
                    }),
        ];
    }
}