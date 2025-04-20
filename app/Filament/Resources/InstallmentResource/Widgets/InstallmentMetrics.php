<?php

namespace App\Filament\Resources\InstallmentResource\Widgets;

use App\Models\PaymentPlan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstallmentMetrics extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Active installment plans
        $activePlans = PaymentPlan::where('status', 'active')->count();

        // Total remaining balance
        $totalRemaining = PaymentPlan::where('status', 'active')
            ->get()
            ->sum(function (PaymentPlan $plan) {
                return $plan->remainingBalance();
            });

        // Overdue plans
        $overduePlans = PaymentPlan::where('status', 'active')
            ->where('final_due_date', '<', now())
            ->count();

        // Plans due this month
        $dueSoon = PaymentPlan::where('status', 'active')
            ->whereMonth('final_due_date', now()->month)
            ->whereYear('final_due_date', now()->year)
            ->count();

        return [
            Stat::make('Active Installment Plans', $activePlans)
                ->description('Total number of active payment plans')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Total Remaining Balance', '₦' . number_format($totalRemaining, 2))
                ->description('Outstanding amount from all active plans')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Overdue Plans', $overduePlans)
                ->description('Payment plans past their due date')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),

            Stat::make('Due This Month', $dueSoon)
                ->description('Plans with final payment due this month')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}