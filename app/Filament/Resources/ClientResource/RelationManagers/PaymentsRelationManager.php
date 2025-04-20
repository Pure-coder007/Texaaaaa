<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Payments';

    protected static ?string $icon = 'heroicon-o-credit-card';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\Select::make('purchase_id')
                            ->relationship('purchase', 'transaction_id')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->prefix('NGN')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_method')
                            ->label('Payment Method')
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_type')
                            ->label('Payment Type')
                            ->disabled(),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Proof')
                    ->schema([
                        Forms\Components\ViewField::make('payment_proof')
                            ->view('filament.forms.components.payment-proof-viewer'),
                    ])
                    ->visible(fn (Payment $record) => $record->paymentProof && $record->paymentProof->hasMedia('proof_documents'))
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.transaction_id')
                    ->label('Transaction')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase.estate.name')
                    ->label('Estate'),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('has_proof')
                    ->label('Proof')
                    ->boolean()
                    ->getStateUsing(fn (Payment $record): bool =>
                        $record->paymentProof && $record->paymentProof->hasMedia('proof_documents')
                    )
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('payment_type')
                    ->options([
                        'outright' => 'Outright',
                        'installment' => 'Installment',
                    ]),
                Tables\Filters\Filter::make('has_proof')
                    ->label('Has Payment Proof')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereHas('paymentProof')
                    ),
            ])
            ->headerActions([
                // Payments are created from the frontend, not from admin
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Allow only status updates
                        return [
                            'status' => $data['status'],
                        ];
                    }),
                Tables\Actions\Action::make('view_proof')
                    ->label('View Proof')
                    ->icon('heroicon-o-document')
                    ->visible(fn (Payment $record): bool =>
                        $record->paymentProof && $record->paymentProof->hasMedia('proof_documents')
                    )
                    ->url(fn (Payment $record): string =>
                        $record->paymentProof->getFirstMediaUrl('proof_documents')
                    )
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // No bulk actions needed for payments
            ])
            ->defaultSort('created_at', 'desc');
    }
}