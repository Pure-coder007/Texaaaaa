<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchasesRelationManager extends RelationManager
{
    protected static string $relationship = 'purchases';

    protected static ?string $recordTitleAttribute = 'transaction_id';

    protected static ?string $title = 'Purchases';

    protected static ?string $icon = 'heroicon-o-shopping-bag';

    public function form(Form $form): Form
    {
        // View-only form since purchases are created through the marketplace
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Details')
                    ->schema([
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->disabled(),
                        Forms\Components\Select::make('estate_id')
                            ->relationship('estate', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_plots')
                            ->label('Total Plots')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->prefix('NGN')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\Select::make('payment_plan_type')
                            ->label('Payment Type')
                            ->options([
                                'outright' => 'Outright',
                                '6_months' => '6 Months Plan',
                                '12_months' => '12 Months Plan',
                            ])
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->disabled(),
                        Forms\Components\DatePicker::make('purchase_date')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_id')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estate.name')
                    ->label('Estate')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_plots')
                    ->label('Plots')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_plan_type')
                    ->label('Payment Type')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'outright' => 'Outright',
                        '6_months' => '6 Months',
                        '12_months' => '12 Months',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'outright' => 'success',
                        '6_months' => 'info',
                        '12_months' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_plan_type')
                    ->label('Payment Type')
                    ->options([
                        'outright' => 'Outright',
                        '6_months' => '6 Months',
                        '12_months' => '12 Months',
                    ]),
            ])
            ->headerActions([
                // Purchases are created from the frontend, not from admin
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\Action::make('view_transaction')
                //     ->label('Go to Transaction')
                //     ->icon('heroicon-o-banknotes')
                //     ->url(fn (Purchase $record): string => route('filament.admin.resources.transactions.view', ['record' => $record]))
                //     ->openUrlInNewTab(),
                // Tables\Actions\Action::make('view_payments')
                //     ->label('Payments')
                //     ->icon('heroicon-o-credit-card')
                //     ->url(fn (Purchase $record): string => route('filament.admin.resources.transactions.view-payments', ['record' => $record]))
                //     ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // No bulk actions needed for purchases
            ])
            ->defaultSort('purchase_date', 'desc');
    }
}