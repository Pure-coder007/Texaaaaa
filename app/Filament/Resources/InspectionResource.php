<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InspectionResource\Pages;
use App\Filament\Resources\InspectionResource\RelationManagers;
use App\Models\Estate;
use App\Models\Inspection;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Property Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Client')
                    ->options(User::where('role', 'client')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('estate_id')
                    ->label('Estate')
                    ->options(Estate::where('status', 'active')->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('plot_id', null)),

                Forms\Components\DatePicker::make('scheduled_date')
                    ->label('Inspection Date')
                    ->required()
                    ->minDate(now())
                    ->helperText('Inspections are scheduled at 10:00 AM'),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label('Additional Notes')
                    ->rows(3)
                    ->maxLength(500),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('estate.name')
                    ->label('Estate')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Date')
                    ->date('F j, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('inspection_time')
                    ->label('Time')
                    ->state('10:00 AM'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested On')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('estate_id')
                    ->label('Estate')
                    ->relationship('estate', 'name'),

                Tables\Filters\Filter::make('scheduled_date')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_from'),
                        Forms\Components\DatePicker::make('scheduled_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['scheduled_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_date', '>=', $date),
                            )
                            ->when(
                                $data['scheduled_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming Inspections')
                    ->query(fn (Builder $query): Builder => $query->whereDate('scheduled_date', '>=', now())),

                Tables\Filters\Filter::make('today')
                    ->label('Today\'s Inspections')
                    ->query(fn (Builder $query): Builder => $query->whereDate('scheduled_date', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (Inspection $record): void {
                        $record->status = 'confirmed';
                        $record->save();

                        Notification::make()
                            ->title('Inspection confirmed successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Inspection $record): bool => $record->status === 'pending'),

                Tables\Actions\Action::make('complete')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(function (Inspection $record): void {
                        $record->status = 'completed';
                        $record->save();

                        Notification::make()
                            ->title('Inspection marked as completed')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Inspection $record): bool =>
                        in_array($record->status, ['pending', 'confirmed'])),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Inspection $record): void {
                        $record->status = 'cancelled';
                        $record->save();

                        Notification::make()
                            ->title('Inspection cancelled')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Inspection $record): bool =>
                        in_array($record->status, ['pending', 'confirmed'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('confirm_selected')
                    ->label('Confirm Selected')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (Collection $records): void {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->status === 'pending') {
                                $record->status = 'confirmed';
                                $record->save();
                                $count++;
                            }
                        }

                        Notification::make()
                            ->title("{$count} inspections confirmed successfully")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('cancel_selected')
                    ->label('Cancel Selected')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $count = 0;
                        foreach ($records as $record) {
                            if (in_array($record->status, ['pending', 'confirmed'])) {
                                $record->status = 'cancelled';
                                $record->save();
                                $count++;
                            }
                        }

                        Notification::make()
                            ->title("{$count} inspections cancelled")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('scheduled_date', 'desc');
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
            'index' => Pages\ListInspections::route('/'),
            'create' => Pages\CreateInspection::route('/create'),
            'edit' => Pages\EditInspection::route('/{record}/edit'),
            'view' => Pages\ViewInspection::route('/{record}'),
        ];
    }
}