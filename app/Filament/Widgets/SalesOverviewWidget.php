<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\Purchase;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SalesOverviewWidget extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        // Calculate total sales
        $totalSales = Purchase::where('status', 'completed')->count();

        // Calculate total revenue (verified payments)
        $totalRevenue = Payment::where('status', 'verified')->sum('amount');

        // Calculate payments due (payment plans with remaining balance)
        $paymentsDue = PaymentPlan::where('status', 'active')
            ->where(function ($query) {
                $query->where('final_due_date', '>=', now());
            })
            ->sum(DB::raw('total_amount - (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.payment_plan_id = payment_plans.id AND payments.status = "verified")'));

        return [
            Stat::make('Total Sales', $totalSales)
                ->description('Completed purchases')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('Total Revenue', 'NGN ' . number_format($totalRevenue, 2))
                ->description('From verified payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([15, 30, 20, 45, 35, 60, 75])
                ->color('success'),

            Stat::make('Payments Due', 'NGN ' . number_format($paymentsDue, 2))
                ->description('From active payment plans')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([40, 35, 30, 25, 30, 22, 19])
                ->color('warning'),
        ];
    }
}