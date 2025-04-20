<?php

namespace App\Filament\Resources\EstateResource\Widgets;

use App\Models\Estate;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class EstateStatsOverview extends BaseWidget
{
    public ?Estate $record = null;

    protected function getStats(): array
    {
        // If there's no record (like on create form), return empty stats
        if (!$this->record) {
            dd($this->record);
            return [];
        }

        return [
            Stat::make('Total Plots', $this->record->plots()->count())
                ->description('All plots in this estate')
                ->descriptionIcon('heroicon-m-map')
                ->color('primary'),

            Stat::make('Available Plots', $this->record->plots()->where('status', 'available')->count())
                ->description('Plots ready for sale')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Reserved Plots', $this->record->plots()->where('status', 'reserved')->count())
                ->description('Currently reserved plots')
                ->descriptionIcon('heroicon-m-bookmark')
                ->color('warning'),

            Stat::make('Sold Plots', $this->record->plots()->where('status', 'sold')->count())
                ->description('Completed sales')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
        ];
    }
}
