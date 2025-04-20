<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PboResource\Pages;
use App\Filament\Resources\PboResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PboResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'PBO Accounts';

    protected static ?string $navigationGroup = 'PBO Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', 'pbo');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('PBO Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pbo_code')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique code for this PBO. Will be auto-generated if left empty.'),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->default('active'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('PBO Level and Commission Settings')
                    ->schema([
                        Forms\Components\Select::make('pbo_level_id')
                            ->relationship('pboLevel', 'name')
                            ->label('PBO Level')
                            ->required(),
                        Forms\Components\Toggle::make('use_custom_commission')
                            ->label('Use Custom Commission Rates')
                            ->helperText('Override the default commission rates from the PBO Level')
                            ->reactive(),
                        Forms\Components\TextInput::make('custom_direct_commission_percentage')
                            ->label('Custom Direct Sale Commission (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->hidden(fn (callable $get) => !$get('use_custom_commission')),
                        Forms\Components\TextInput::make('custom_referral_commission_percentage')
                            ->label('Custom Referral Commission (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->hidden(fn (callable $get) => !$get('use_custom_commission')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Banking Details')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_number')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_branch')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bank_swift_code')
                            ->maxLength(255),
                        Forms\Components\Select::make('preferred_payment_method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'mobile_money' => 'Mobile Money',
                                'cash' => 'Cash',
                            ]),
                        Forms\Components\Textarea::make('payment_notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pbo_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pboLevel.name')
                    ->label('PBO Level')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pboSales_count')
                    ->label('Sales Count')
                    ->counts('pboSales')
                    ->sortable(),
                Tables\Columns\TextColumn::make('referrals_count')
                    ->label('Referrals')
                    ->counts('referrals')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'suspended' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
                Tables\Filters\SelectFilter::make('pbo_level_id')
                    ->label('PBO Level')
                    ->relationship('pboLevel', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('change_status')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                    'suspended' => 'Suspended',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SalesRelationManager::class,
            RelationManagers\CommissionsRelationManager::class,
            RelationManagers\ReferralsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPbos::route('/'),
            // 'view' => Pages\ViewPbo::route('/{record}'),
            'edit' => Pages\EditPbo::route('/{record}/edit'),
        ];
    }
}