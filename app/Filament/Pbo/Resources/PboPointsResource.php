<?php

namespace App\Filament\Pbo\Resources;

use App\Filament\Pbo\Resources\PboPointsResource\Pages;
use App\Filament\Pbo\Resources\PboPointsResource\Widgets;
use App\Models\PboPoint;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PboPointsResource extends Resource
{
    protected static ?string $model = PboPoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'My Points';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('pbo_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Points are system-generated, no need for form fields
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('points')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'sale' => 'Sale',
                        'referral' => 'Referral',
                        'bonus' => 'Bonus',
                    ]),
            ])
            ->actions([
                // No actions needed as points are system-generated
            ])
            ->bulkActions([
                // No bulk actions needed
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPboPoints::route('/'),
        ];
    }
}