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

class EstatePerformanceWidget extends \Filament\Widgets\ChartWidget
{
    protected static ?string $heading = 'Estate Sales Performance';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $estates = Estate::withCount([
            'purchases' => function ($query) {
                $query->where('status', 'completed');
            }
        ])
        ->withSum([
            'purchases' => function ($query) {
                $query->where('status', 'completed');
            }
        ], 'total_amount')
        ->orderByDesc('purchases_count')
        ->limit(10)
        ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales (NGN)',
                    'data' => $estates->pluck('purchases_sum_total_amount')->toArray(),
                    'backgroundColor' => '#36A2EB',
                ],
                [
                    'label' => 'Number of Sales',
                    'data' => $estates->pluck('purchases_count')->toArray(),
                    'backgroundColor' => '#FF6384',
                ],
            ],
            'labels' => $estates->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}