<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentResource\Pages;
use App\Filament\Resources\InstallmentResource\RelationManagers;
use App\Models\PaymentPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstallmentResource extends Resource
{
    protected static ?string $model = PaymentPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Installment Plans';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([ ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.estate.name')
                    ->label('Estate')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('initial_payment')
                    ->label('Initial Payment')
                    ->money('NGN')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('duration_months')
                    ->label('Duration')
                    ->suffix(' months')
                    ->sortable(),
                Tables\Columns\TextColumn::make('totalPaid')
                    ->label('Paid Amount')
                    ->money('NGN')
                    ->getStateUsing(fn (PaymentPlan $record): float => $record->totalPaid())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // This would require a subquery in a real implementation
                        return $query->orderBy('total_amount', $direction);
                    }),
                Tables\Columns\TextColumn::make('remainingBalance')
                    ->label('Balance')
                    ->money('NGN')
                    ->getStateUsing(fn (PaymentPlan $record): float => $record->remainingBalance())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('total_amount', $direction);
                    }),
                // Tables\Columns\ProgressColumn::make('payment_progress')
                //     ->label('Progress')
                //     ->getStateUsing(function (PaymentPlan $record): float {
                //         $percentage = ($record->totalPaid() / $record->total_amount) * 100;
                //         return $percentage;
                //     }),
                Tables\Columns\TextColumn::make('final_due_date')
                    ->label('Final Due Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'primary',
                        'completed' => 'success',
                        'defaulted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'defaulted' => 'Defaulted',
                    ]),
                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Plans')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', 'active')
                        ->where('final_due_date', '<', now())
                    ),
                Tables\Filters\Filter::make('due_this_month')
                    ->label('Due This Month')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', 'active')
                        ->whereMonth('final_due_date', now()->month)
                        ->whereYear('final_due_date', now()->year)
                    ),
                Tables\Filters\SelectFilter::make('purchase.estate_id')
                    ->label('Estate')
                    ->relationship('purchase.estate', 'name'),
            ])
            ->actions([

            ])
            ->bulkActions([

            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallments::route('/'),
            // 'edit' => Pages\EditInstallment::route('/{record}/edit'),
        ];
    }
}