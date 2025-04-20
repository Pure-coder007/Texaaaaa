<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\MyDocumentsResource\Pages;
use App\Models\ClientFolder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyDocumentsResource extends Resource
{
    protected static ?string $model = ClientFolder::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'My Documents';

    protected static ?string $modelLabel = 'My Documents';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('client_id', Auth::id());
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
            ->recordUrl(fn (ClientFolder $record): string => static::getUrl('documents', ['folder' => $record]))
            ->actions([
                Tables\Actions\ViewAction::make('view_documents')
                    ->label('View Documents')
                    ->url(fn (ClientFolder $record): string => static::getUrl('documents', ['folder' => $record]))
                    ->icon('heroicon-o-document-text')
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
            'index' => Pages\ListFolders::route('/'),
            'documents' => Pages\ListDocuments::route('/{folder}/documents'),
        ];
    }
}