<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Filament\Resources\TransactionResource\Pages\VerifyPayment;
use App\Filament\Resources\TransactionResource\Pages\ViewPayments;
use App\Filament\Resources\TransactionResource\Pages\ViewTransaction;
use App\Filament\Resources\TransactionResource\RelationManagers\PaymentsRelationManager;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PaymentProof;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;

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
        // View-only form since transactions are auto-generated
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->disabled(),
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'name')
                            ->disabled(),
                        Forms\Components\Select::make('pbo_id')
                            ->relationship('pbo', 'name')
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
                        Forms\Components\TextInput::make('status')
                            ->disabled(),
                        Forms\Components\DatePicker::make('purchase_date')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pbo.name')
                    ->label('PBO')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('estate.name')
                    ->label('Estate')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_plots')
                    ->label('Plots')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('NGN')
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
                Tables\Columns\TextColumn::make('remainingBalance')
                    ->label('Balance')
                    ->money('NGN')
                    ->getStateUsing(fn (Purchase $record): float => $record->remainingBalance())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('total_amount', $direction);
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('estate')
                    ->relationship('estate', 'name'),
                Filter::make('has_balance')
                    ->label('With Balance Due')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.purchase_id = purchases.id AND payments.status = "verified") < purchases.total_amount')),

            ])
            ->actions([
                Tables\Actions\Action::make('download_latest_proof')
                ->label('Download Proof')
                ->icon('heroicon-o-document-arrow-down')
                ->visible(fn (Purchase $record): bool =>
                    $record->payments()
                        ->whereHas('paymentProof', function ($query) {
                            $query->whereHas('media', function ($mediaQuery) {
                                $mediaQuery->where('collection_name', 'proof_documents');
                            });
                        })
                        ->exists()
                )
                ->action(function (Purchase $record) {
                    // Get the latest payment with proof
                    $payment = $record->payments()
                        ->whereHas('paymentProof', function ($query) {
                            $query->whereHas('media', function ($mediaQuery) {
                                $mediaQuery->where('collection_name', 'proof_documents');
                            });
                        })
                        ->latest()
                        ->first();

                    if ($payment && $payment->paymentProof) {
                        // Get the media item
                        $media = $payment->paymentProof->getFirstMedia('proof_documents');

                        if ($media) {
                            // Force download the file instead of opening it
                            return response()->download(
                                $media->getPath(),
                                $media->file_name,
                                ['Content-Type' => $media->mime_type]
                            );
                        }
                    }

                    // If no proof found, show an error notification
                    Notification::make()
                        ->title('No payment proof found')
                        ->danger()
                        ->send();
                }),

                Tables\Actions\Action::make('view_latest_proof')
                ->label('View Proof')
                ->icon('heroicon-o-document')
                ->visible(fn (Purchase $record): bool =>
                    $record->payments()
                        ->whereHas('paymentProof', function ($query) {
                            $query->whereHas('media', function ($mediaQuery) {
                                $mediaQuery->where('collection_name', 'proof_documents');
                            });
                        })
                        ->exists()
                )
                ->url(function (Purchase $record) {
                    // Get the latest payment with proof
                    $payment = $record->payments()
                        ->whereHas('paymentProof', function ($query) {
                            $query->whereHas('media', function ($mediaQuery) {
                                $mediaQuery->where('collection_name', 'proof_documents');
                            });
                        })
                        ->latest()
                        ->first();

                    if ($payment && $payment->paymentProof) {
                        return $payment->paymentProof->getFirstMediaUrl('proof_documents');
                    }

                    return null;
                })
                ->openUrlInNewTab(),

            ])
            ->defaultSort('purchase_date', 'desc')
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            // PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            // 'view' => ViewTransaction::route('/{record}'),
        ];
    }
}