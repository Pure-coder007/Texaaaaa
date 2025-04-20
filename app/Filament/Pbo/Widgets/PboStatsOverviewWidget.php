<?php

namespace App\Filament\Pbo\Widgets;

use App\Models\PboSale;
use App\Models\Purchase;
use App\Models\PboReferral;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PboStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pboId = auth()->id();

        // Sales statistics
        $totalSales = Purchase::where('pbo_id', $pboId)->count();
        $pendingSales = Purchase::where('pbo_id', $pboId)
            ->where('status', 'pending')
            ->count();
        $completedSales = Purchase::where('pbo_id', $pboId)
            ->where('status', 'completed')
            ->count();

        // Get sales trend data for current month compared to previous month
        $currentMonthSales = Purchase::where('pbo_id', $pboId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $previousMonthSales = Purchase::where('pbo_id', $pboId)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $salesTrend = $previousMonthSales > 0
            ? round((($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100, 1)
            : ($currentMonthSales > 0 ? 100 : 0);

        $salesTrendDescription = $salesTrend >= 0
            ? "{$salesTrend}% increase from last month"
            : abs($salesTrend) . "% decrease from last month";

        // Commission statistics
        $totalCommission = PboSale::where('pbo_id', $pboId)->sum('commission_amount');
        $pendingCommission = PboSale::where('pbo_id', $pboId)
            ->where('status', 'pending')
            ->sum('commission_amount');
        $paidCommission = PboSale::where('pbo_id', $pboId)
            ->where('status', 'paid')
            ->sum('commission_amount');

        // Get monthly commission data for chart
        $monthlyCommissions = [];
        for ($i = 0; $i < 6; $i++) {
            $date = now()->subMonths(5 - $i)->startOfMonth();
            $commission = PboSale::where('pbo_id', $pboId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('commission_amount');

            $monthlyCommissions[] = round($commission / 1000, 1); // Convert to thousands for cleaner chart
        }

        // Referral statistics
        $totalReferrals = PboReferral::where('referrer_id', $pboId)->count();
        $convertedReferrals = PboReferral::where('referrer_id', $pboId)
            ->where('status', 'converted')
            ->count();

        return [
            Stat::make('Total Sales', $totalSales)
                ->description("{$completedSales} completed, {$pendingSales} pending")
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->chart($salesTrend >= 0 ? [0, 2, 1, 3, 2, $salesTrend ?: 3] : [3, 2, 3, 1, 2, abs($salesTrend) ?: 0])
                ->color($salesTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Total Commission', '₦' . number_format($totalCommission, 2))
                ->description('₦' . number_format($pendingCommission, 2) . ' pending')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($monthlyCommissions)
                ->color('primary'),

            Stat::make('Referrals', $totalReferrals)
                ->description("{$convertedReferrals} converted")
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
        ];
    }
}