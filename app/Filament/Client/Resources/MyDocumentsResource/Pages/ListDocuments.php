<?php

namespace App\Filament\Client\Resources\MyDocumentsResource\Pages;

use App\Filament\Client\Resources\MyDocumentsResource;
use App\Models\ClientDocument;
use App\Models\ClientFolder;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListDocuments extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = MyDocumentsResource::class;

    protected static string $view = 'filament.client.resources.my-documents-resource.pages.list-documents';

    public ClientFolder $folder;

    public function mount(ClientFolder $folder): void
    {
        // Ensure the client can only access their own folders
        if ($folder->client_id !== auth()->id()) {
            abort(403);
        }

        $this->folder = $folder;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_folders')
                ->label('Back to Folders')
                ->icon('heroicon-o-arrow-left')
                ->url(MyDocumentsResource::getUrl()),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->folder->documents()->getQuery())
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->searchable()
                        ->weight('bold')
                        ->size('lg')
                        ->alignCenter(),
                    Tables\Columns\TextColumn::make('document_type')
                        ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state)))
                        ->color('gray')
                        ->alignCenter(),
                    Tables\Columns\TextColumn::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'pending' => 'warning',
                            'client_signed' => 'info',
                            'admin_signed' => 'primary',
                            'completed' => 'success',
                            'rejected' => 'danger',
                            default => 'gray',
                        })
                        ->icon(fn (string $state): string => match ($state) {
                            'pending' => 'heroicon-o-document',
                            'client_signed' => 'heroicon-o-check-circle',
                            'admin_signed' => 'heroicon-o-check-circle',
                            'completed' => 'heroicon-o-check-badge',
                            'rejected' => 'heroicon-o-x-circle',
                            default => 'heroicon-o-document',
                        })
                        ->alignCenter(),
                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->alignCenter()
                        ->size('sm')
                        ->color('gray'),
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->options([
                        'receipt' => 'Receipt',
                        'contract' => 'Sales Contract',
                        'allocation' => 'Allocation Letter',
                        'deed' => 'Deed of Assignment',
                        'survey' => 'Survey Plan',
                        'agreement' => 'Purchase Agreement',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'client_signed' => 'Client Signed',
                        'admin_signed' => 'Admin Signed',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (ClientDocument $record): string =>
                        $record->getFirstMediaUrl('document_file')
                    )
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('client_sign')
                    ->label('Sign Document')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        FileUpload::make('signed_document')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->required()
                            ->disk('public'),
                    ])
                    ->action(function (ClientDocument $record, array $data) {
                        // Clear any existing files in the collection
                        $record->clearMediaCollection('signed_document');

                        // Get the uploaded file path
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
                //
            ]);
    }

    public function getHeading(): string
    {
        return "Documents in {$this->folder->name}";
    }
}