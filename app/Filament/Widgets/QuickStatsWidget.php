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

class QuickStatsWidget extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        // Calculate available plots
        $availablePlots = Plot::where('status', 'available')->count();

        // Count active PBOs
        $activePBOs = User::where('role', 'pbo')
            ->where('status', 'active')
            ->count();

        // Count active clients
        $activeClients = User::where('role', 'client')
            ->where('status', 'active')
            ->count();

        return [
            Stat::make('Available Plots', $availablePlots)
                ->description('Ready for purchase')
                ->descriptionIcon('heroicon-m-map')
                ->color('success'),

            Stat::make('Active PBOs', $activePBOs)
                ->description('Private Business Owners')
                ->descriptionIcon('heroicon-m-user')
                ->color('primary'),

            Stat::make('Active Clients', $activeClients)
                ->description('Registered customers')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}