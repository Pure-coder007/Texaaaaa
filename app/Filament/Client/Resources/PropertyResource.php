<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\PropertyResource\Pages;
use App\Filament\Client\Resources\PropertyResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PropertyResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'My Properties';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Property';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', Auth::id());
    }



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
            //     Infolists\Components\Section::make('Transaction Details')
            //         ->schema([
            //             Infolists\Components\TextEntry::make('transaction_id')
            //                 ->label('Transaction ID'),
            //             Infolists\Components\TextEntry::make('total_plots')
            //                 ->label('Number of Plots'),
            //             Infolists\Components\TextEntry::make('total_area')
            //                 ->label('Total Area (sqm)'),
            //             Infolists\Components\TextEntry::make('total_amount')
            //                 ->label('Total Amount')
            //                 ->money('NGN'),
            //             Infolists\Components\TextEntry::make('purchase_date')
            //                 ->label('Purchase Date')
            //                 ->date(),
            //             Infolists\Components\TextEntry::make('payment_plan_type')
            //                 ->label('Payment Plan')
            //                 ->formatStateUsing(fn (string $state): string => match($state) {
            //                     'outright' => 'Outright Payment',
            //                     '6_months' => '6 Months Installment',
            //                     '12_months' => '12 Months Installment',
            //                     default => $state,
            //                 }),
            //             Infolists\Components\TextEntry::make('status')
            //                 ->label('Status')
            //                 ->badge()
            //                 ->color(fn (string $state): string => match ($state) {
            //                     'pending' => 'warning',
            //                     'completed' => 'success',
            //                     'cancelled' => 'danger',
            //                     default => 'gray',
            //                 }),
            //         ])
            //         ->columns(2),

            //     Infolists\Components\Section::make('Estate Information')
            //         ->schema([
            //             Infolists\Components\TextEntry::make('estate.name')
            //                 ->label('Estate Name'),
            //             Infolists\Components\TextEntry::make('estate.city.name')
            //                 ->label('City'),
            //             Infolists\Components\TextEntry::make('estate.location.name')
            //                 ->label('Location'),
            //             Infolists\Components\TextEntry::make('estate.address')
            //                 ->label('Address')
            //                 ->columnSpanFull(),
            //         ])
            //         ->columns(2),

            //     Infolists\Components\Section::make('Payment Summary')
            //         ->schema([
            //             Infolists\Components\TextEntry::make('totalPaid')
            //                 ->label('Total Paid')
            //                 ->state(fn (Purchase $record): float => $record->totalPaid())
            //                 ->money('NGN'),
            //             Infolists\Components\TextEntry::make('remainingBalance')
            //                 ->label('Remaining Balance')
            //                 ->state(fn (Purchase $record): float => $record->remainingBalance())
            //                 ->money('NGN'),
            //             Infolists\Components\TextEntry::make('paymentProgress')
            //                 ->label('Payment Progress')
            //                 ->state(fn (Purchase $record): float =>
            //                     ($record->totalPaid() / $record->total_amount) * 100
            //                 ),
            //             Infolists\Components\TextEntry::make('paymentStatus')
            //                 ->label('Payment Status')
            //                 ->state(function (Purchase $record): string {
            //                     if ($record->remainingBalance() <= 0) {
            //                         return 'Fully Paid';
            //                     } elseif ($record->paymentPlan && $record->paymentPlan->status === 'defaulted') {
            //                         return 'Defaulted';
            //                     } elseif ($record->paymentPlan && $record->paymentPlan->status === 'active') {
            //                         return 'In Progress';
            //                     } else {
            //                         return 'Pending';
            //                     }
            //                 })
            //                 ->badge()
            //                 ->color(fn (string $state): string => match ($state) {
            //                     'Fully Paid' => 'success',
            //                     'Defaulted' => 'danger',
            //                     'In Progress' => 'primary',
            //                     default => 'warning',
            //                 }),
            //         ])
            //         ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_plots')
                    ->label('Plots'),
                Tables\Columns\TextColumn::make('total_area')
                    ->label('Area (sqm)'),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_plan_type')
                    ->label('Payment Plan')
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
                Tables\Columns\TextColumn::make('payment_progress')
                    ->label('Payment Progress')
                    ->getStateUsing(function (Purchase $record): float {
                        return ($record->totalPaid() / $record->total_amount) * 100;
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->groups([
                Tables\Grouping\Group::make('estate.name')
                    ->label('Estate')
                    ->collapsible(),
            ])
            ->defaultGroup('estate.name')
            ->filters([
                // Clients typically won't need to filter their own properties
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->label('View Documents'),
            ])
            ->bulkActions([
                // No bulk actions needed for client properties view
            ])
            ->defaultSort('purchase_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\PurchasePlotsRelationManager::class,
            // RelationManagers\PaymentsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
             'view' => Pages\ViewProperty::route('/{record}'),
            // 'plots' => Pages\ViewPlots::route('/{record}/plots'),
            // 'documents' => Pages\ViewDocuments::route('/{record}/documents'),
        ];
    }
}