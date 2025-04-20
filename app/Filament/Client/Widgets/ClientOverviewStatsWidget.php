<?php

namespace App\Filament\Client\Widgets;

use App\Models\Purchase;
use App\Models\ClientDocument;
use App\Models\Payment;
use App\Models\Inspection;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ClientOverviewStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $clientId = auth()->id();

        // Get total properties value
        $propertiesValue = Purchase::where('client_id', $clientId)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        // Get total paid amount
        $totalPaid = Payment::where('client_id', $clientId)
            ->where('status', 'verified')
            ->sum('amount');

        // Get total documents count
        $documentsCount = ClientDocument::query()
            ->whereHas('folder', function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })
            ->count();

        // Get count of properties
        $propertiesCount = Purchase::where('client_id', $clientId)
            ->where('status', '!=', 'cancelled')
            ->count();

        return [
            Stat::make('Total Properties', $propertiesCount)
                ->description('Purchased plots')
                ->descriptionIcon('heroicon-m-home')
                ->color('success'),

            Stat::make('Properties Value', 'NGN ' . number_format($propertiesValue, 2))
                ->description('Total investment')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Amount Paid', 'NGN ' . number_format($totalPaid, 2))
                ->description('Total payments made')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('info'),

            Stat::make('Documents', $documentsCount)
                ->description('Available in your account')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
        ];
    }
}