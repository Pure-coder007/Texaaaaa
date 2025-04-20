<?php

namespace App\Filament\Exports;

use App\Models\PboSale;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PboCommissionExporter extends Exporter
{
    protected static ?string $model = PboSale::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Commission ID'),
            ExportColumn::make('pbo.name')
                ->label('PBO Name'),
            ExportColumn::make('pbo.phone')
                ->label('PBO Phone'),
            ExportColumn::make('pbo.bank_name')
                ->label('Bank Name'),
            ExportColumn::make('pbo.bank_account_number')
                ->label('Account Number'),
            ExportColumn::make('pbo.bank_account_name')
                ->label('Account Name'),
            ExportColumn::make('purchase.transaction_id')
                ->label('Transaction ID'),
            ExportColumn::make('purchase.estate.name')
                ->label('Estate'),
            ExportColumn::make('client.name')
                ->label('Client'),
            ExportColumn::make('sale_type')
                ->label('Sale Type')
                ->formatStateUsing(fn (string $state): string => match($state) {
                    'direct' => 'Direct Sale',
                    'referral' => 'Referral',
                    default => $state,
                }),
            ExportColumn::make('commission_percentage')
                ->label('Commission Rate')
                ->formatStateUsing(fn (string $state): string => $state . '%'),
            ExportColumn::make('commission_amount')
                ->label('Commission Amount')
                ->formatStateUsing(fn (string $state): string => '₦' . number_format((float)$state, 2)),
            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn (string $state): string => match($state) {
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'paid' => 'Paid',
                    default => $state,
                }),
            ExportColumn::make('payment_date')
                ->label('Payment Date'),
            ExportColumn::make('payment_reference')
                ->label('Payment Reference'),
            ExportColumn::make('created_at')
                ->label('Commission Date'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your PBO commission export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}