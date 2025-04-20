<?php

namespace App\Filament\Resources\DocumentManagementResource\Pages;

use App\Filament\Resources\DocumentManagementResource;
use App\Models\ClientFolder;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListClientFolders extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = DocumentManagementResource::class;

    protected static string $view = 'filament.resources.document-management-resource.pages.list-client-folders';

    public User $client;

    public function mount(User $client): void
    {
        $this->client = $client;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_clients')
                ->label('Back to Clients')
                ->icon('heroicon-o-arrow-left')
                ->url(DocumentManagementResource::getUrl()),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ClientFolder::query()->where('client_id', $this->client->id)
            )
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\IconColumn::make('folder_icon')
                        ->icon('heroicon-o-folder')
                        ->color(fn (ClientFolder $record): string => match($record->folder_type) {
                            'purchase' => 'success',
                            'subscription' => 'primary',
                            default => 'warning',
                        })
                        ->size('xl')
                        ->alignCenter(),
                    Tables\Columns\TextColumn::make('name')
                        ->searchable()
                        ->sortable()
                        ->weight('bold')
                        ->alignCenter()
                        ->size('lg'),
                    Tables\Columns\TextColumn::make('folder_type')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => ucfirst($state))
                        ->alignCenter(),
                    Tables\Columns\TextColumn::make('documents_count')
                        ->counts('documents')
                        ->label('Documents')
                        ->alignCenter()
                        ->badge(),
                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->alignCenter()
                        ->size('sm')
                        ->color('gray'),
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('folder_type')
                    ->options([
                        'purchase' => 'Purchase',
                        'subscription' => 'Subscription',
                        'general' => 'General',
                    ]),
                Tables\Filters\Filter::make('has_documents')
                    ->label('Has Documents')
                    ->query(fn (Builder $query): Builder =>
                        $query->has('documents')),
            ])
            ->recordUrl(fn (ClientFolder $record): string => DocumentManagementResource::getUrl('folder-documents', ['client' => $this->client, 'folder' => $record]))
            ->actions([
                Tables\Actions\ViewAction::make('view_documents')
                    ->label('View Documents')
                    ->url(fn (ClientFolder $record): string => DocumentManagementResource::getUrl('folder-documents', ['client' => $this->client, 'folder' => $record]))
                    ->icon('heroicon-o-document-text')
                    ->color('primary'),
            ])
            ->bulkActions([
                //
            ]);
    }

    public function getHeading(): string
    {
        return "Folders for {$this->client->name}";
    }
}