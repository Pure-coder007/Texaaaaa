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

class OverduePaymentsAlertWidget extends \Filament\Widgets\TableWidget
{
    protected static ?string $heading = 'Overdue Payments';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                PaymentPlan::with(['client', 'purchase'])
                    ->where('status', 'active')
                    ->where('final_due_date', '<', now())
                    ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.payment_plan_id = payment_plans.id AND payments.status = "verified") < payment_plans.total_amount')
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('purchase.estate.name')
                    ->label('Estate'),
                \Filament\Tables\Columns\TextColumn::make('final_due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('NGN')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Remaining Balance')
                    ->money('NGN')
                    ->getStateUsing(fn (PaymentPlan $record): float => $record->remainingBalance())
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('overdue_days')
                    ->label('Days Overdue')
                    ->getStateUsing(fn (PaymentPlan $record): int => Carbon::parse($record->final_due_date)->diffInDays(now(), false))
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state > 30 => 'danger',
                        $state > 14 => 'warning',
                        default => 'gray',
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('send_reminder')
                    ->label('Send Reminder')
                    ->icon('heroicon-o-envelope')
                    ->action(function (PaymentPlan $record) {
                        // Logic to send payment reminder
                        // This would typically dispatch a notification or email job
                    })
            ])
            ->paginated([5, 10, 25, 50]);
}
}