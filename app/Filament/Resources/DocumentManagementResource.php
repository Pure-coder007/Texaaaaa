<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentManagementResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentManagementResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Document Management';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Client Documents';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', 'client')
            ->has('clientFolders');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->contentGrid([
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('avatar_url')
                        ->circular()
                        ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=6366F1')
                        ->alignCenter(),
                    Tables\Columns\TextColumn::make('name')
                        ->searchable()
                        ->sortable()
                        ->weight('bold')
                        ->alignCenter()
                        ->size('lg'),
                    Tables\Columns\TextColumn::make('email')
                        ->searchable()
                        ->alignCenter()
                        ->color('gray'),
                    Tables\Columns\TextColumn::make('phone')
                        ->searchable()
                        ->alignCenter()
                        ->color('gray'),
                    Tables\Columns\TextColumn::make('clientFolders_count')
                        ->counts('clientFolders')
                        ->label('Folders')
                        ->alignCenter()
                        ->badge(),
                ]),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_documents')
                    ->label('Has Documents')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereHas('clientFolders.documents')),
            ])
            ->recordUrl(fn (User $record): string => static::getUrl('client-folders', ['client' => $record]))
            ->actions([
                Tables\Actions\ViewAction::make('view_folders')
                    ->label('View Folders')
                    ->url(fn (User $record): string => static::getUrl('client-folders', ['client' => $record]))
                    ->icon('heroicon-o-folder-open')
                    ->color('primary'),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'client-folders' => Pages\ListClientFolders::route('/{client}/folders'),
            'folder-documents' => Pages\ListFolderDocuments::route('/{client}/folders/{folder}/documents'),
        ];
    }
}