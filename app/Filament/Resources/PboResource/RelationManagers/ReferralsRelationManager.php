<?php

namespace App\Filament\Resources\PboResource\RelationManagers;

use App\Models\PboReferral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'referrals';

    protected static ?string $recordTitleAttribute = 'email';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'converted' => 'Converted',
                        'expired' => 'Expired',
                    ])
                    ->required(),
                Forms\Components\Select::make('referred_id')
                    ->relationship('referred', 'name')
                    ->label('Converted User')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('converted_at')
                    ->label('Converted At')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires At'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'converted' => 'success',
                        'expired' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('referred.name')
                    ->label('Converted User'),
                Tables\Columns\TextColumn::make('converted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'converted' => 'Converted',
                        'expired' => 'Expired',
                    ]),
            ])
            ->headerActions([
                // No create action as per your requirements
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_as_expired')
                    ->icon('heroicon-o-clock')
                    ->color('danger')
                    ->visible(fn (PboReferral $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (PboReferral $record) {
                        $record->update([
                            'status' => 'expired',
                            'expires_at' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_as_expired')
                        ->label('Mark Selected as Expired')
                        ->icon('heroicon-o-clock')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'expired',
                                        'expires_at' => now(),
                                    ]);
                                }
                            });
                        }),
                ]),
            ]);
    }
}