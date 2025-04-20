<?php

namespace App\Filament\Client\Resources\PropertyResource\RelationManagers;

use App\Models\ClientDocument;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Property Documents';

    protected static ?string $icon = 'heroicon-o-document-text';

    // We're customizing this to handle the indirect relationship properly
    protected function getTableQuery(): Builder
    {
        // Get the purchase record
        $purchase = $this->getOwnerRecord();

        // Get the client folder ID associated with this purchase
        $clientFolderId = $purchase->clientFolder?->id;

        // If no folder exists, return an empty query
        if (!$clientFolderId) {
            return ClientDocument::query()->whereRaw('1 = 0'); // Empty result set
        }

        // Query documents from this specific folder
        return ClientDocument::query()
            ->where('client_folder_id', $clientFolderId);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Document Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'receipt' => 'success',
                        'contract' => 'primary',
                        'allocation' => 'warning',
                        'payment_plan' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('requires_client_signature')
                    ->label('Requires Your Signature')
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil-square')
                    ->falseIcon('heroicon-o-check')
                    ->trueColor('warning')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('client_signature_status')
                    ->label('Your Signature')
                    ->getStateUsing(function (ClientDocument $record): string {
                        if (!$record->requires_client_signature) {
                            return 'Not Required';
                        }

                        return $record->client_signed_at
                            ? 'Signed on ' . $record->client_signed_at->format('M d, Y')
                            : 'Awaiting Signature';
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'Not Required') {
                            return 'gray';
                        }

                        return str_contains($state, 'Signed') ? 'success' : 'warning';
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'client_signed' => 'Awaiting Admin Signature',
                        'admin_signed' => 'Awaiting Your Signature',
                        'completed' => 'Completed',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'client_signed' => 'info',
                        'admin_signed' => 'primary',
                        'completed' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->options([
                        'receipt' => 'Receipt',
                        'contract' => 'Contract',
                        'allocation' => 'Allocation',
                        'payment_plan' => 'Payment Plan',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'client_signed' => 'Awaiting Admin Signature',
                        'admin_signed' => 'Awaiting Your Signature',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\Filter::make('needs_your_signature')
                    ->label('Needs Your Signature')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('requires_client_signature', true)
                              ->whereNull('client_signed_at')
                    ),
            ])
            ->headerActions([
                // No create action for clients
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (ClientDocument $record): string =>
                        $record->getFirstMediaUrl('document_file')
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (ClientDocument $record): bool =>
                        $record->getFirstMedia('document_file') !== null
                    ),
                    Tables\Actions\Action::make('download_signed')
                    ->label('Download Signed')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->url(fn (ClientDocument $record): string =>
                        $record->getFirstMediaUrl('signed_document')
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (ClientDocument $record): bool =>
                        ($record->client_signed_at || $record->admin_signed_at) &&
                        $record->getFirstMedia('signed_document') !== null
                    ),
                    Tables\Actions\Action::make('client_sign')
                    ->label('Sign Document')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        FileUpload::make('signed_document')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(10240)
                        ->required()
                        ->disk('public')
                    ])
                    ->action(function (ClientDocument $record, array $data) {
                         // Clear any existing files in the collection
                         $record->clearMediaCollection('signed_document');

                         // Get the uploaded file path from the data
                         $filePath = $data['signed_document'];

                         // Add the file to the media collection
                         $record->addMediaFromDisk($filePath, 'public')
                                ->toMediaCollection('signed_document');


                        // Mark the document as signed by the client
                        $record->client_signed_at = now();

                        // Update the status if needed
                        if ($record->status === 'admin_signed' || $record->status === 'pending') {
                            $record->status = 'client_signed';
                        }

                        // If both signatures are present, mark as completed
                        if ($record->isFullySigned()) {
                            $record->status = 'completed';
                        }

                        $record->save();

                        // Add notification
                        Notification::make()
                            ->title('Document signed successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ClientDocument $record): bool =>
                        $record->requires_client_signature &&
                        $record->client_signed_at === null
                    ),
            ])
            ->bulkActions([
                // No bulk actions needed for client view
            ])
            ->emptyStateHeading('No Documents Available')
            ->emptyStateDescription('Documents related to this property will appear here once generated.')
            ->defaultSort('created_at', 'desc');
    }
}