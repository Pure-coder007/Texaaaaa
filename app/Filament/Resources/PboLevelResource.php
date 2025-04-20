<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PboLevelResource\Pages;
use App\Models\PboLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PboLevelResource extends Resource
{
    protected static ?string $model = PboLevel::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $navigationLabel = 'PBO Levels';

    protected static ?string $navigationGroup = 'PBO Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('PBO Level Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('direct_sale_commission_percentage')
                            ->required()
                            ->label('Direct Sale Commission (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\TextInput::make('referral_commission_percentage')
                            ->required()
                            ->label('Referral Commission (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\TextInput::make('minimum_sales_count')
                            ->label('Minimum Sales Count')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('minimum_sales_value')
                            ->label('Minimum Sales Value')
                            ->required()
                            ->numeric()
                            ->prefix('NGN')
                            ->minValue(0),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->default('active')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('direct_sale_commission_percentage')
                    ->label('Direct Sale Commission')
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('referral_commission_percentage')
                    ->label('Referral Commission')
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_sales_count')
                    ->label('Min. Sales Count')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_sales_value')
                    ->label('Min. Sales Value')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('PBO Count')
                    ->counts('users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPboLevels::route('/'),
            'create' => Pages\CreatePboLevel::route('/create'),
            'edit' => Pages\EditPboLevel::route('/{record}/edit'),
        ];
    }
}