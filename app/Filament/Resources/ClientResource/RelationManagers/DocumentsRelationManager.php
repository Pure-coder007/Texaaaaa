<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\ClientDocument;
use App\Models\ClientFolder;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'clientFolders';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Documents';

    protected static ?string $icon = 'heroicon-o-document-text';

    // We're customizing this to show documents across all folders
    protected function getTableQuery(): Builder
    {
        // Get the client ID
        $clientId = $this->getOwnerRecord()->id;

        // Query all documents from all folders for this client
        return ClientDocument::query()
            ->whereHas('folder', function (Builder $query) use ($clientId) {
                $query->where('client_id', $clientId);
            });
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_folder_id')
                    ->label('Folder')
                    ->options(fn () =>
                        ClientFolder::where('client_id', $this->getOwnerRecord()->id)
                            ->pluck('name', 'id')
                    )
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('document_type')
                    ->label('Document Type')
                    ->options([
                        'receipt' => 'Receipt',
                        'contract' => 'Contract',
                        'allocation' => 'Allocation',
                        'payment_plan' => 'Payment Plan',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\FileUpload::make('document_file')
                    ->label('Document File')
                    ->disk('public')
                    ->directory('client-documents')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(10240) // 10MB
                    ->required()
                    ->visible(function (string $operation) {
                        return $operation === 'create';
                    }),
                Forms\Components\Toggle::make('requires_client_signature')
                    ->label('Requires Client Signature'),
                Forms\Components\Toggle::make('requires_admin_signature')
                    ->label('Requires Admin Signature'),
                Forms\Components\Toggle::make('is_system_generated')
                    ->label('System Generated Document')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'client_signed' => 'Client Signed',
                        'admin_signed' => 'Admin Signed',
                        'completed' => 'Completed',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Document Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('folder.name')
                    ->label('Folder')
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Upload Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_system_generated')
                    ->label('System Generated')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\IconColumn::make('client_signature_status')
                    ->label('Client Signature')
                    ->boolean()
                    ->getStateUsing(fn (ClientDocument $record): bool =>
                        $record->requires_client_signature && $record->client_signed_at !== null
                    )
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon(fn (ClientDocument $record): string =>
                        $record->requires_client_signature ? 'heroicon-o-clock' : 'heroicon-o-minus'
                    )
                    ->trueColor('success')
                    ->falseColor(fn (ClientDocument $record): string =>
                        $record->requires_client_signature ? 'warning' : 'gray'
                    ),
                Tables\Columns\IconColumn::make('admin_signature_status')
                    ->label('Admin Signature')
                    ->boolean()
                    ->getStateUsing(fn (ClientDocument $record): bool =>
                        $record->requires_admin_signature && $record->admin_signed_at !== null
                    )
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon(fn (ClientDocument $record): string =>
                        $record->requires_admin_signature ? 'heroicon-o-clock' : 'heroicon-o-minus'
                    )
                    ->trueColor('success')
                    ->falseColor(fn (ClientDocument $record): string =>
                        $record->requires_admin_signature ? 'warning' : 'gray'
                    ),
                Tables\Columns\TextColumn::make('status')
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
                Tables\Filters\SelectFilter::make('folder')
                    ->relationship('folder', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'client_signed' => 'Client Signed',
                        'admin_signed' => 'Admin Signed',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\Filter::make('requires_signature')
                    ->query(fn (Builder $query): Builder =>
                        $query->where(function($query) {
                            $query->where('requires_client_signature', true)
                                  ->orWhere('requires_admin_signature', true);
                        })
                    ),
                Tables\Filters\Filter::make('system_generated')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('is_system_generated', true)
                    ),
            ])
            ->groups([
                Tables\Grouping\Group::make('folder.name')
                    ->label('Folder')
                    ->collapsible(),
            ])
            ->defaultGroup('folder.name')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Only allow certain fields to be updated
                        return [
                            'client_folder_id' => $data['client_folder_id'],
                            'name' => $data['name'],
                            'document_type' => $data['document_type'],
                            'requires_client_signature' => $data['requires_client_signature'],
                            'requires_admin_signature' => $data['requires_admin_signature'],
                            'status' => $data['status'],
                        ];
                    }),
                Tables\Actions\Action::make('download')
                    ->label('Download Original')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (ClientDocument $record): string =>
                        $record->getFirstMediaUrl('document_file')
                    )
                    ->openUrlInNewTab(),

                    // Admin sign action
            Tables\Actions\Action::make('admin_sign')
            ->label('Sign Document')
            ->icon('heroicon-o-pencil-square')
            ->color('primary')
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

                // Mark the document as signed by the admin
                $record->admin_signed_at = now();
                $record->admin_signer_id = auth()->id();

                // Update the status if needed
                if ($record->status === 'client_signed' || $record->status === 'pending') {
                    $record->status = 'admin_signed';
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
                $record->requires_admin_signature &&
                $record->admin_signed_at === null
            ),

            // Download signed document action
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

            ])
            ->bulkActions([
                // No bulk actions needed for documents
            ])
            ->defaultSort('created_at', 'desc');
    }
}