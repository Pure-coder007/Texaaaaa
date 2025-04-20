<?php

namespace App\Filament\Widgets;

use App\Models\Payment;

class PaymentTrendsWidget extends \Filament\Widgets\ChartWidget
{
    protected static ?string $heading = 'Payment Trends (Last 12 Months)';

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect();
        $paymentData = collect();

        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push($date->format('M Y'));

            $monthlySales = Payment::where('status', 'verified')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');

            $paymentData->push($monthlySales);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Payments (NGN)',
                    'data' => $paymentData->toArray(),
                    'fill' => false,
                    'borderColor' => '#4BC0C0',
                    'tension' => 0.1
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}