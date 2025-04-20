<?php

namespace App\Filament\Resources\EstateResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\EstatePlotType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;

class PlotsRelationManager extends RelationManager
{
    protected static string $relationship = 'plots';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Estate Plots';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plotType.name')
                    ->label('Plot Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dimensions')
                    ->label('Dimensions')
                    ->searchable(),

                TextColumn::make('area')
                    ->label('Area')
                    ->numeric()
                    ->suffix(' sqm')
                    ->sortable(),

                TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),

                IconColumn::make('is_corner')
                    ->label('Corner')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_commercial')
                    ->label('Commercial')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'reserved' => 'warning',
                        'sold' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->defaultSort('plotType.name')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'reserved' => 'Reserved',
                        'sold' => 'Sold',
                    ])
                    ->label('Status'),

                SelectFilter::make('plot_type')
                    ->relationship('plotType', 'name')
                    ->label('Plot Type'),

                SelectFilter::make('is_corner')
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ])
                    ->label('Corner Plot'),

                SelectFilter::make('is_commercial')
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ])
                    ->label('Commercial Plot'),
            ])
            ->groups([
                Group::make('plotType.name')
                    ->label('Plot Type')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn ($record) => "{$record->plotType->name} Plots")
                    ->getDescriptionFromRecordUsing(function ($record) {
                        $estateId = $this->getOwnerRecord()->id;
                        $plotTypeId = $record->estate_plot_type_id;

                        $totalPlots = $this->getRelationship()
                            ->where('estate_plot_type_id', $plotTypeId)
                            ->count();

                        $availablePlots = $this->getRelationship()
                            ->where('estate_plot_type_id', $plotTypeId)
                            ->where('status', 'available')
                            ->count();

                        $reservedPlots = $this->getRelationship()
                            ->where('estate_plot_type_id', $plotTypeId)
                            ->where('status', 'reserved')
                            ->count();

                        $soldPlots = $this->getRelationship()
                            ->where('estate_plot_type_id', $plotTypeId)
                            ->where('status', 'sold')
                            ->count();

                        return "Total: {$totalPlots} | Available: {$availablePlots} | Reserved: {$reservedPlots} | Sold: {$soldPlots}";
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->with('plotType')
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ]);
            });
    }
}