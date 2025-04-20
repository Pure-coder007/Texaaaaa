<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PboCommissionExporter;
use App\Filament\Resources\CommissionResource\Pages;
use App\Models\PboSale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ExportAction;

class CommissionResource extends Resource
{
    protected static ?string $model = PboSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Commission Management';

    protected static ?string $navigationGroup = 'PBO Management';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return static::getModel()::where('status', 'pending')->count() > 0
            ? 'warning'
            : 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Commission Details')
                    ->schema([
                        Forms\Components\Select::make('pbo_id')
                            ->relationship('pbo', 'name')
                            ->label('PBO')
                            ->disabled()
                            ->required(),
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'name')
                            ->label('Client')
                            ->disabled()
                            ->required(),
                        Forms\Components\Select::make('purchase_id')
                            ->relationship('purchase', 'transaction_id')
                            ->label('Transaction')
                            ->disabled()
                            ->required(),
                        Forms\Components\Select::make('sale_type')
                            ->options([
                                'direct' => 'Direct Sale',
                                'referral' => 'Referral',
                            ])
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('commission_percentage')
                            ->label('Commission Percentage')
                            ->suffix('%')
                            ->disabled()
                            ->numeric(),
                        Forms\Components\TextInput::make('commission_amount')
                            ->label('Commission Amount')
                            ->prefix('NGN')
                            ->disabled()
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
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pbo.name')
                    ->label('PBO')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pbo.pbo_code')
                    ->label('PBO Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.transaction_id')
                    ->label('Transaction ID')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('commission_percentage')
                    ->label('Rate')
                    ->suffix('%')
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('pbo_id')
                    ->label('PBO')
                    ->relationship('pbo', 'name')
                    ->searchable(),
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
            ->headerActions([
                ExportAction::make()
                    ->label('Export Report')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exporter(PboCommissionExporter::class),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissions::route('/'),
            // 'view' => Pages\ViewCommission::route('/{record}'),
            'edit' => Pages\EditCommission::route('/{record}/edit'),
        ];
    }
}