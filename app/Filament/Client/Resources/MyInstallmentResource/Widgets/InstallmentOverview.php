<?php

namespace App\Filament\Client\Resources\MyInstallmentResource\Widgets;

use App\Models\PaymentPlan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class InstallmentOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get client's payment plans
        $userId = Auth::id();
        $paymentPlans = PaymentPlan::where('client_id', $userId)->get();

        // Calculate stats
        $activeInstallments = $paymentPlans->where('status', 'active')->count();
        $completedInstallments = $paymentPlans->where('status', 'completed')->count();
        $defaultedInstallments = $paymentPlans->where('status', 'defaulted')->count();

        // Calculate totals
        $totalPaymentAmount = $paymentPlans->sum(function ($plan) {
            return $plan->totalPaid();
        });

        $totalRemainingAmount = $paymentPlans->sum(function ($plan) {
            return $plan->remainingBalance();
        });

        // Get upcoming payment due dates
        $upcomingDueDates = $paymentPlans
            ->where('status', 'active')
            ->sortBy('final_due_date')
            ->take(1)
            ->first();

        $nextDueDate = $upcomingDueDates ? $upcomingDueDates->final_due_date->format('M d, Y') : 'No upcoming due dates';

        return [
            Stat::make('Active Installment Plans', $activeInstallments)
                ->description('Payment plans in progress')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Completed Installment Plans', $completedInstallments)
                ->description('Payment plans fully paid')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Amount Paid', '₦' . number_format($totalPaymentAmount, 2))
                ->description('Across all payment plans')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Remaining', '₦' . number_format($totalRemainingAmount, 2))
                ->description('Balance to be paid')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($totalRemainingAmount > 0 ? 'warning' : 'success'),

            Stat::make('Next Due Date', $nextDueDate)
                ->description('For active payment plans')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($upcomingDueDates && now()->greaterThan($upcomingDueDates->final_due_date) ? 'danger' : 'primary'),
        ];
    }
}