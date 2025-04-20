<?php

namespace App\Filament\Pbo\Resources\PboPointsResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PointsSummaryWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        // Get point counts
        $totalPoints = $user->getTotalPointsAttribute();
        $salesPoints = $user->pboPoints()->where('type', 'sale')->sum('points');
        $referralPoints = $user->pboPoints()->where('type', 'referral')->sum('points');
        $bonusPoints = $user->pboPoints()->where('type', 'bonus')->sum('points');

        return [
            Stat::make('Total Points', $totalPoints)
                ->icon('heroicon-o-star')
                ->description('Your current point balance')
                ->color('primary')
                ->chart([
                    $salesPoints,
                    $referralPoints,
                    $bonusPoints
                ]),

            Stat::make('From Referrals', $referralPoints)
                ->icon('heroicon-o-user-plus')
                ->description('Points earned from referrals')
                ->color('info'),

            Stat::make('From Sales', $salesPoints)
                ->icon('heroicon-o-currency-dollar')
                ->description('Points earned from sales')
                ->color('success'),
        ];
    }
}