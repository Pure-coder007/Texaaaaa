<?php

namespace App\Filament\Pbo\Resources;

use App\Filament\Pbo\Resources\CommissionTrackingResource\Pages;
use App\Filament\Pbo\Resources\CommissionTrackingResource\Widgets\CommissionOverviewWidget;
use App\Filament\Pbo\Resources\CommissionTrackingResource\Widgets\CommissionsByMonthChart;
use App\Models\PboSale;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommissionTrackingResource extends Resource
{
    protected static ?string $model = PboSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Commission Tracking';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('pbo_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // View-only form as commissions are system-generated
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchase.transaction_id')
                    ->label('Transaction ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sale_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'direct' => 'success',
                        'referral' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('purchase.total_amount')
                    ->label('Sale Amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_percentage')
                    ->label('Commission %')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission Amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'paid' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sale Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sale_type')
                    ->options([
                        'direct' => 'Direct Sale',
                        'referral' => 'Referral Sale',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'paid' => 'Paid',
                    ]),
                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('payment_from'),
                        \Filament\Forms\Components\DatePicker::make('payment_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['payment_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['payment_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
               
            ])
            ->bulkActions([
                // No bulk actions needed
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                'status',
                'sale_type',
                'payment_date',
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissionTrackings::route('/'),
            // 'view' => Pages\ViewCommission::route('/{record}'),
        ];
    }
}