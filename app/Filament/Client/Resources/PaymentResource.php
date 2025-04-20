<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'My Payments';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        // A minimal form since we won't use create/edit
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.estate.name')
                    ->label('Estate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Payment Type')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'outright' => 'Outright',
                        'installment' => 'Installment',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'outright' => 'success',
                        'installment' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bank_transfer' => 'primary',
                        'cash' => 'success',
                        'credit_card' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Payment Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_type')
                    ->options([
                        'outright' => 'Outright',
                        'installment' => 'Installment',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From ' . $data['from']->format('M d, Y');
                        }

                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Until ' . $data['until']->format('M d, Y');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('download_receipt')
                    ->label(fn (Payment $record): string =>
                        $record->payment_type === 'installment' ? 'Download Installment Receipt' : 'Download Receipt'
                    )
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(function (Payment $record): ?string {
                        // For installment payments, look for installment receipt
                        if ($record->payment_type === 'installment') {
                            $installmentReceipt = \App\Models\ClientDocument::query()
                                ->whereHas('folder', function ($query) use ($record) {
                                    $query->where('client_id', $record->client_id)
                                        ->where('purchase_id', $record->purchase_id);
                                })
                                ->where('document_type', 'installment_receipt')
                                ->where('metadata->payment_id', $record->id)
                                ->where('status', 'completed')
                                ->first();

                            if ($installmentReceipt) {
                                return $installmentReceipt->getFirstMediaUrl('document_file');
                            }
                        }

                        // For outright payments or as a fallback, look for regular receipt
                        $regularReceipt = \App\Models\ClientDocument::query()
                            ->whereHas('folder', function ($query) use ($record) {
                                $query->where('client_id', $record->client_id)
                                    ->where('purchase_id', $record->purchase_id);
                            })
                            ->where('document_type', 'receipt')
                            ->where('status', 'completed')
                            ->first();

                        return $regularReceipt?->getFirstMediaUrl('document_file');
                    })
                    ->openUrlInNewTab()
                    ->visible(function (Payment $record): bool {
                        // Only show for verified payments with available receipt
                        if ($record->status !== 'verified') {
                            return false;
                        }

                        // Check for installment receipt first if it's an installment payment
                        if ($record->payment_type === 'installment') {
                            $hasInstallmentReceipt = \App\Models\ClientDocument::query()
                                ->whereHas('folder', function ($query) use ($record) {
                                    $query->where('client_id', $record->client_id)
                                        ->where('purchase_id', $record->purchase_id);
                                })
                                ->where('document_type', 'installment_receipt')
                                ->where('metadata->payment_id', $record->id)
                                ->where('status', 'completed')
                                ->exists();

                            if ($hasInstallmentReceipt) {
                                return true;
                            }
                        }

                        // Fall back to checking for regular receipt
                        return \App\Models\ClientDocument::query()
                            ->whereHas('folder', function ($query) use ($record) {
                                $query->where('client_id', $record->client_id)
                                    ->where('purchase_id', $record->purchase_id);
                            })
                            ->where('document_type', 'receipt')
                            ->where('status', 'completed')
                            ->exists();
                    }),
            ])
            ->bulkActions([
                // No bulk actions needed for this view
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // No relations needed for client payment view
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
            // No create or edit pages
        ];
    }
}