<?php

namespace App\Filament\Resources\PboResource\RelationManagers;

use App\Models\PboSale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'pboSales';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Commissions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('purchase_id')
                    ->relationship('purchase', 'transaction_id')
                    ->label('Transaction')
                    ->disabled(),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name')
                    ->label('Client')
                    ->disabled(),
                Forms\Components\TextInput::make('commission_amount')
                    ->label('Commission Amount')
                    ->disabled()
                    ->prefix('NGN')
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'paid' => 'Paid',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Payment Date')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'paid'),
                Forms\Components\TextInput::make('payment_reference')
                    ->label('Payment Reference')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'paid'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('purchase.transaction_id')
                    ->label('Transaction ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client'),
                Tables\Columns\TextColumn::make('sale_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'direct' => 'Direct Sale',
                        'referral' => 'Referral',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'direct' => 'success',
                        'referral' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'paid' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_reference')
                    ->label('Payment Ref'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'paid' => 'Paid',
                    ]),
                Tables\Filters\SelectFilter::make('sale_type')
                    ->options([
                        'direct' => 'Direct Sale',
                        'referral' => 'Referral',
                    ]),
            ])
            ->headerActions([
                // No create action as per your requirements
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PboSale $record) => $record->status === 'pending')
                    ->action(function (PboSale $record) {
                        $record->update(['status' => 'approved']);
                    }),
                Tables\Actions\Action::make('mark_as_paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->visible(fn (PboSale $record) => $record->status === 'approved')
                    ->form([
                        Forms\Components\DatePicker::make('payment_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('payment_reference')
                            ->required(),
                    ])
                    ->action(function (PboSale $record, array $data) {
                        $record->update([
                            'status' => 'paid',
                            'payment_date' => $data['payment_date'],
                            'payment_reference' => $data['payment_reference'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update(['status' => 'approved']);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_selected_as_paid')
                        ->label('Mark Selected as Paid')
                        ->icon('heroicon-o-banknotes')
                        ->color('primary')
                        ->form([
                            Forms\Components\DatePicker::make('payment_date')
                                ->required()
                                ->default(now()),
                            Forms\Components\TextInput::make('payment_reference')
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                if ($record->status === 'approved') {
                                    $record->update([
                                        'status' => 'paid',
                                        'payment_date' => $data['payment_date'],
                                        'payment_reference' => $data['payment_reference'],
                                    ]);
                                }
                            });
                        }),
                ]),
            ]);
    }
}