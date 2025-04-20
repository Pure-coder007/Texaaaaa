<?php

namespace App\Filament\Pbo\Widgets;

use App\Models\PboSale;
use App\Models\Purchase;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PboSalesPerformanceWidget extends ChartWidget
{
    protected static ?string $heading = 'Sales & Commission Performance';

    protected static ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $pboId = auth()->id();
        $months = collect();

        // Get data for the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push($date->format('M'));

            $monthlySales[$date->format('M')] = Purchase::where('pbo_id', $pboId)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $monthlyCommission[$date->format('M')] = PboSale::where('pbo_id', $pboId)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('commission_amount');

            $directSales[$date->format('M')] = PboSale::where('pbo_id', $pboId)
                ->where('sale_type', 'direct')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $referralSales[$date->format('M')] = PboSale::where('pbo_id', $pboId)
                ->where('sale_type', 'referral')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sales Count',
                    'data' => $months->map(fn ($month) => $monthlySales[$month] ?? 0)->toArray(),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                    'tension' => 0.1,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Commission (₦ thousands)',
                    'data' => $months->map(fn ($month) => round(($monthlyCommission[$month] ?? 0) / 1000, 1))->toArray(),
                    'backgroundColor' => '#FF6384',
                    'borderColor' => '#FF6384',
                    'tension' => 0.1,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Sales Count'
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Commission (₦ thousands)'
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}