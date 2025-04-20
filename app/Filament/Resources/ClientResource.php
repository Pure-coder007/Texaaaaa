<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages\CreateClient;
use App\Filament\Resources\ClientResource\Pages\EditClient;
use App\Filament\Resources\ClientResource\Pages\ListClients;
use App\Filament\Resources\ClientResource\Pages\ViewClient;
use App\Filament\Resources\ClientResource\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\ClientResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\ClientResource\RelationManagers\PurchasesRelationManager;
use App\Models\User;
use App\Models\Purchase;
use App\Models\ClientDocument;
use App\Models\Payment;
use App\Models\Inspection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $navigationGroup = 'Client Management';

    protected static ?string $modelLabel = 'Client';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'client');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->tel(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('spouse_name'),
                        Forms\Components\DatePicker::make('date_of_birth'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                        Forms\Components\Select::make('marital_status')
                            ->options([
                                'single' => 'Single',
                                'married' => 'Married',
                                'divorced' => 'Divorced',
                                'widowed' => 'Widowed',
                            ]),
                        Forms\Components\TextInput::make('nationality'),
                        Forms\Components\TagsInput::make('languages_spoken'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->rows(3),
                        Forms\Components\TextInput::make('country_of_residence'),
                        Forms\Components\TextInput::make('mobile_number'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Employment Details')
                    ->schema([
                        Forms\Components\TextInput::make('occupation'),
                        Forms\Components\TextInput::make('employer_name'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Next of Kin')
                    ->schema([
                        Forms\Components\TextInput::make('next_of_kin_name'),
                        Forms\Components\TextInput::make('next_of_kin_relationship'),
                        Forms\Components\Textarea::make('next_of_kin_address')
                            ->rows(3),
                        Forms\Components\TextInput::make('next_of_kin_phone'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=6366F1'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchases_count')
                    ->label('Purchases')
                    ->counts('purchases')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => 'suspended',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
                Tables\Filters\Filter::make('with_purchases')
                    ->query(fn (Builder $query): Builder => $query->whereHas('purchases')),
                Tables\Filters\Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records) {
                            // Export logic here
                        }),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Client Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\ImageEntry::make('avatar_url')
                                    ->label('Avatar')
                                    ->circular()
                                    ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=6366F1')
                                    ->columnSpan(1),

                                Infolists\Components\Group::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Full Name'),
                                        Infolists\Components\TextEntry::make('email'),
                                        Infolists\Components\TextEntry::make('phone'),
                                    ])
                                    ->columnSpan(1),

                                Infolists\Components\Group::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'active' => 'success',
                                                'inactive' => 'danger',
                                                'suspended' => 'warning',
                                                default => 'gray',
                                            }),
                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label('Joined Date')
                                            ->date(),
                                        Infolists\Components\TextEntry::make('email_verified_at')
                                            ->label('Verified')
                                            ->placeholder('Not verified')
                                            ->date(),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ]),

                Infolists\Components\Section::make('Personal Information')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('date_of_birth')
                                    ->date(),
                                Infolists\Components\TextEntry::make('gender'),
                                Infolists\Components\TextEntry::make('marital_status'),
                                Infolists\Components\TextEntry::make('nationality'),
                                Infolists\Components\TextEntry::make('spouse_name'),
                                Infolists\Components\TextEntry::make('languages_spoken')->listWithLineBreaks(),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Contact Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('address')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('country_of_residence'),
                        Infolists\Components\TextEntry::make('mobile_number'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Employment Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('occupation'),
                        Infolists\Components\TextEntry::make('employer_name'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Next of Kin')
                    ->schema([
                        Infolists\Components\TextEntry::make('next_of_kin_name'),
                        Infolists\Components\TextEntry::make('next_of_kin_relationship'),
                        Infolists\Components\TextEntry::make('next_of_kin_address')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('next_of_kin_phone'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Activity Overview')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('purchases_count')
                                    ->state(fn (User $record): int => $record->purchases()->count())
                                    ->label('Purchases'),

                                Infolists\Components\TextEntry::make('active_purchases_count')
                                    ->state(fn (User $record): int => $record->purchases()->where('status', 'pending')->count())
                                    ->label('Active Purchases'),

                                Infolists\Components\TextEntry::make('total_spent')
                                    ->money('NGN')
                                    ->state(fn (User $record): float => $record->payments()->where('status', 'verified')->sum('amount'))
                                    ->label('Total Spent'),

                                Infolists\Components\TextEntry::make('inspections_count')
                                    ->state(fn (User $record): int => $record->inspections()->count())
                                    ->label('Inspections'),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PurchasesRelationManager::class,
            PaymentsRelationManager::class,
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
            'view' => ViewClient::route('/{record}'),
        ];
    }
}