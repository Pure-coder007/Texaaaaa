<?php

namespace App\Filament\Widgets;

use App\Models\Estate;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\Plot;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PboPerformanceWidget extends \Filament\Widgets\ChartWidget
{
    protected static ?string $heading = 'Top-Performing PBOs';

    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $topPbos = User::where('role', 'pbo')
            ->withCount(['pboSales' => function ($query) {
                $query->where('status', 'approved')->orWhere('status', 'paid');
            }])
            ->withSum(['pboSales' => function ($query) {
                $query->where('status', 'approved')->orWhere('status', 'paid');
            }], 'commission_amount')
            ->orderByDesc('pbo_sales_sum_commission_amount')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Commission Earned (NGN)',
                    'data' => $topPbos->pluck('pbo_sales_sum_commission_amount')->toArray(),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#8AC249', '#EA526F', '#23B5D3', '#279AF1'
                    ],
                ],
            ],
            'labels' => $topPbos->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}