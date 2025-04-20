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

class RecentTransactionsWidget extends \Filament\Widgets\TableWidget
{
    protected static ?string $heading = 'Recent Transactions';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                Payment::with(['client', 'purchase'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('payment_type')
                    ->label('Type')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method'),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('NGN')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'failed' => 'danger',
                        'pending' => 'warning',
                        'verified' => 'success',
                        default => 'gray',
                    }),
            ])
            ->paginated([5, 10, 25, 50]);
}
}