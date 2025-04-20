<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstateResource\Pages;
use App\Filament\Resources\EstateResource\RelationManagers;
use App\Models\Estate;
use App\Services\PlotGenerationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\Group as InfolistGroup;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class EstateResource extends Resource
{
    protected static ?string $model = Estate::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Property Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Estate Management')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                Section::make('Estate Information')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(['md' => 2]),

                                        Select::make('location_id')
                                            ->relationship('location', 'name', function (Builder $query) {
                                                return $query->with(['city', 'city.state', 'city.state.country']);
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                Select::make('city_id')
                                                    ->relationship('city', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload(),
                                                TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('latitude')
                                                            ->label('Latitude')
                                                            ->numeric()
                                                            ->step(0.000001)
                                                            ->rules(['required', 'numeric', 'min:-90', 'max:90'])
                                                            ->placeholder('e.g., 6.5244'),
                                                        TextInput::make('longitude')
                                                            ->label('Longitude')
                                                            ->numeric()
                                                            ->step(0.000001)
                                                            ->rules(['required', 'numeric', 'min:-180', 'max:180'])
                                                            ->placeholder('e.g., 3.3792'),
                                                    ]),
                                                Forms\Components\View::make('filament.forms.components.coordinates-help'),
                                            ])
                                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                                "{$record->name}, {$record->city->name}, {$record->city->state->name}"
                                            )
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, $state) {
                                                if ($state) {
                                                    $location = \App\Models\Location::with('city')->find($state);
                                                    if ($location) {
                                                        $set('city_id', $location->city_id);
                                                    }
                                                }
                                            }),

                                        Hidden::make('city_id'),

                                        TextInput::make('total_area')
                                            ->required()
                                            ->numeric()
                                            ->suffix('sqm')
                                            ->step(0.01),

                                        Textarea::make('address')
                                            ->required()
                                            ->columnSpan(['md' => 2]),

                                        Textarea::make('description')
                                            ->maxLength(65535)
                                            ->columnSpan(['md' => 2]),

                                        Select::make('status')
                                            ->options([
                                                'active' => 'Active',
                                                'inactive' => 'Inactive',
                                                'development' => 'Under Development',
                                                'completed' => 'Completed',
                                            ])
                                            ->default('active')
                                            ->required(),

                                        Select::make('manager_id')
                                            ->relationship('manager', 'name', function (Builder $query) {
                                                return $query->where('role', 'admin')
                                                    ->where(function($q) {
                                                        $q->where('admin_role', 'estate_manager')
                                                          ->orWhere('admin_role', 'super_admin');
                                                    });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ])
                                    ->columns(['md' => 2]),

                                Section::make('Documents & Media')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('estate_images')
                                            ->collection('estate_images')
                                            ->multiple()
                                            ->image()
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('16:9')
                                            ->imageResizeTargetWidth('1920')
                                            ->imageResizeTargetHeight('1080')
                                            ->helperText('Upload estate photos (optimal size: 1920x1080)')
                                            ->columnSpan(['md' => 2])
                                            ->reorderable(),

                                        SpatieMediaLibraryFileUpload::make('featured_image')
                                            ->collection('featured_image')
                                            ->image()
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('16:9')
                                            ->helperText('Featured image for the estate')
                                            ->columnSpan(['md' => 1]),

                                        SpatieMediaLibraryFileUpload::make('site_plan')
                                            ->collection('site_plan')
                                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                                            ->helperText('Site plan document')
                                            ->columnSpan(['md' => 1]),

                                        SpatieMediaLibraryFileUpload::make('estate_plans')
                                            ->collection('estate_plans')
                                            ->multiple()
                                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                                            ->helperText('Estate plans and layouts')
                                            ->columnSpan(['md' => 2]),

                                        SpatieMediaLibraryFileUpload::make('documents')
                                            ->collection('documents')
                                            ->multiple()
                                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                            ->helperText('Legal documents, deeds, certificates')
                                            ->columnSpan(['md' => 2]),
                                    ])
                                    ->columns(['md' => 2])
                                    ->collapsible(),
                            ]),

                        Tab::make('Plot Types & Pricing')
                            ->schema([
                                Section::make('Premium Settings')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('commercial_plot_premium_percentage')
                                                    ->label('Commercial Plot Premium (%)')
                                                    ->numeric()
                                                    ->default(10)
                                                    ->required()
                                                    ->suffix('%')
                                                    ->helperText('Additional percentage charged for commercial plots'),

                                                TextInput::make('corner_plot_premium_percentage')
                                                    ->label('Corner Plot Premium (%)')
                                                    ->numeric()
                                                    ->default(10)
                                                    ->required()
                                                    ->suffix('%')
                                                    ->helperText('Additional percentage charged for corner plots'),
                                            ]),
                                    ]),

                                Section::make('Plot Types')
                                    ->schema([
                                        Repeater::make('plotTypes')
                                            ->relationship()
                                            ->schema([

                                                Select::make('name')
                                                    ->required()
                                                    ->options([
                                                        'Residential' => 'Residential',
                                                        'Commercial' => 'Commercial',
                                                        'Residential Corner' => 'Residential Corner',
                                                        'Commercial Corner' => 'Commercial Corner',
                                                    ])
                                                    ->helperText('Select the plot type category'),

                                                TextInput::make('size_sqm')
                                                    ->label('Size (sqm)')
                                                    ->required()
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->suffix('sqm'),

                                                Grid::make(3)
                                                    ->schema([
                                                        TextInput::make('outright_price')
                                                            ->label('Outright Payment Price')
                                                            ->required()
                                                            ->numeric()
                                                            ->prefix('₦')
                                                            ->step(1),

                                                        TextInput::make('six_month_price')
                                                            ->label('6 Months Installment Price')
                                                            ->required()
                                                            ->numeric()
                                                            ->prefix('₦')
                                                            ->step(1),

                                                        TextInput::make('twelve_month_price')
                                                            ->label('12 Months Installment Price')
                                                            ->required()
                                                            ->numeric()
                                                            ->prefix('₦')
                                                            ->step(1),
                                                    ]),

                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('plot_count')
                                                            ->label('Number of Plots')
                                                            ->helperText('Total number of plots of this type to generate')
                                                            ->numeric()
                                                            ->integer()
                                                            ->minValue(0)
                                                            ->default(0),

                                                        // TextInput::make('starting_number')
                                                        //     ->label('Starting Plot Number')
                                                        //     ->helperText('The number for the first plot')
                                                        //     ->numeric()
                                                        //     ->integer()
                                                        //     ->minValue(1)
                                                        //     ->default(1),
                                                    ]),



                                                Toggle::make('is_active')
                                                    ->label('Active')
                                                    ->default(true)
                                                    ->helperText('Enable or disable this plot type'),


                                            ])
                                            ->columnSpanFull()
                                            ->columns(2)
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                            ->addActionLabel('Add Plot Type')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->collapsed(false)
                                            ->defaultItems(1)
                                            ->minItems(1),
                                    ]),
                            ]),

                        Tab::make('Promotions')
                            ->schema([
                                Section::make('Estate Promotions')
                                    ->description('Define special promotions like "Buy X Get Y Free" for this estate.')
                                    ->schema([
                                        Repeater::make('promos')
                                            ->relationship()
                                            ->schema([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(100)
                                                    ->placeholder('Buy 5 Get 1 Free')
                                                    ->helperText('Promotion name or title'),

                                                Textarea::make('description')
                                                    ->rows(2)
                                                    ->placeholder('Special launch promotion: Purchase 5 plots and get 1 plot free!')
                                                    ->helperText('Detailed description of the promotion'),

                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('buy_quantity')
                                                            ->label('Buy Quantity')
                                                            ->required()
                                                            ->numeric()
                                                            ->integer()
                                                            ->minValue(1)
                                                            ->default(5)
                                                            ->helperText('Number of plots customer needs to buy'),

                                                            TextInput::make('free_quantity')
                                                            ->label('Free Quantity')
                                                            ->required()
                                                            ->numeric()
                                                            ->integer()
                                                            ->minValue(1)
                                                            ->default(1)
                                                            ->helperText('Number of free plots customer will receive'),
                                                    ]),

                                                Grid::make(2)
                                                    ->schema([
                                                        DatePicker::make('valid_from')
                                                            ->label('Valid From')
                                                            ->required()
                                                            ->default(now())
                                                            ->helperText('When this promotion starts'),

                                                        DatePicker::make('valid_to')
                                                            ->label('Valid Until')
                                                            ->required()
                                                            ->default(now()->addMonths(3))
                                                            ->helperText('When this promotion ends'),
                                                    ]),

                                                Toggle::make('is_active')
                                                    ->label('Active')
                                                    ->default(true)
                                                    ->helperText('Enable or disable this promotion'),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                            ->addActionLabel('Add Promotion')
                                            ->collapsible()
                                            ->defaultItems(0)
                                            ->collapsed(false)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Promo Codes')
                                    ->description('Create discount codes for special offers.')
                                    ->schema([
                                        Repeater::make('promoCodes')
                                            ->relationship()
                                            ->schema([
                                                TextInput::make('code')
                                                    ->required()
                                                    ->maxLength(20)
                                                    ->placeholder('SUMMER2025')
                                                    ->helperText('Promo code for customers to use'),

                                                Select::make('discount_type')
                                                    ->options([
                                                        'percentage' => 'Percentage (%)',
                                                        'fixed' => 'Fixed Amount',
                                                    ])
                                                    ->required()
                                                    ->default('percentage'),

                                                TextInput::make('discount_amount')
                                                    ->required()
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->helperText('Amount or percentage of discount'),

                                                Grid::make(2)
                                                    ->schema([
                                                        DatePicker::make('valid_from')
                                                            ->label('Valid From')
                                                            ->required()
                                                            ->default(now()),

                                                        DatePicker::make('valid_until')
                                                            ->label('Valid Until')
                                                            ->required()
                                                            ->default(now()->addMonths(3)),
                                                    ]),

                                                TextInput::make('usage_limit')
                                                    ->label('Usage Limit')
                                                    ->numeric()
                                                    ->integer()
                                                    ->minValue(1)
                                                    ->placeholder('Leave empty for unlimited uses'),

                                                Select::make('status')
                                                    ->options([
                                                        'active' => 'Active',
                                                        'inactive' => 'Inactive',
                                                        'expired' => 'Expired',
                                                    ])
                                                    ->required()
                                                    ->default('active'),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['code'] ?? null)
                                            ->addActionLabel('Add Promo Code')
                                            ->collapsible()
                                            ->collapsed(false)
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Additional Information')
                            ->schema([
                                Section::make('FAQ')
                                    ->description('Frequently Asked Questions about this estate.')
                                    ->schema([
                                        Repeater::make('faq')
                                            ->schema([
                                                TextInput::make('question')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),

                                                Textarea::make('answer')
                                                    ->required()
                                                    ->rows(3)
                                                    ->columnSpanFull(),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                                            ->addActionLabel('Add FAQ Item')
                                            ->collapsible()
                                            ->collapsed()
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ])

                                    ->collapsible(),

                                Section::make('Terms & Conditions')
                                    ->description('Terms and conditions specific to this estate.')
                                    ->schema([
                                        Repeater::make('terms')
                                            ->schema([
                                                TextInput::make('title')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),

                                                Textarea::make('content')
                                                    ->required()
                                                    ->rows(4)
                                                    ->columnSpanFull(),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                            ->addActionLabel('Add Term')
                                            ->collapsible()
                                            ->collapsed()
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(true),

                                Section::make('Refund Policy')
                                    ->description('Refund policy specific to this estate.')
                                    ->schema([
                                        Repeater::make('refund_policy')
                                            ->schema([
                                                TextInput::make('title')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),

                                                Textarea::make('content')
                                                    ->required()
                                                    ->rows(4)
                                                    ->columnSpanFull(),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                            ->addActionLabel('Add Refund Policy Item')
                                            ->collapsible()
                                            ->collapsed()
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(true),
                            ]),
                    ])
                    ->columnSpanFull()
            ])
            ->columns(1);
    }




    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured_image')
                    ->collection('featured_image')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=111827')
                    ->label(''),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->wrap(),

                TextColumn::make('location.name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->location?->city?->name)
                    ->wrap(),

                TextColumn::make('total_area')
                    ->sortable()
                    ->suffix(' sqm'),

                TextColumn::make('plots_count')
                    ->label('Plots')
                    ->counts('plots')
                    ->sortable(),

                TextColumn::make('manager.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('status')
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'inactive' => 'heroicon-o-x-circle',
                        'development' => 'heroicon-o-wrench-screwdriver',
                        'completed' => 'heroicon-o-flag',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'development' => 'warning',
                        'completed' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'development' => 'Under Development',
                        'completed' => 'Completed',
                    ]),

                SelectFilter::make('manager')
                    ->relationship('manager', 'name'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                // Tables\Actions\Action::make('generatePlots')
                //         ->label('Generate Plots')
                //         ->icon('heroicon-o-arrow-path')
                //         ->action(function (Estate $record) {
                //             $plotGenerator = app(PlotGenerationService::class);
                //             $plotGenerator->generatePlotsForEstate($record);

                //             Notification::make()
                //                 ->title('Plots generated successfully')
                //                 ->success()
                //                 ->send();
                //         })
                //         ->requiresConfirmation()
                //         ->modalHeading('Generate Plots')
                //         ->modalDescription('This will generate plots based on the plot types defined for this estate. Any existing plots will remain unchanged.')
                //         ->modalSubmitActionLabel('Generate'),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make()
                    ->action(function (Model $record): void {
                        try {
                            DB::beginTransaction();

                            // Handle purchases first
                            foreach ($record->purchases as $purchase) {
                                // Delete purchase plots
                                $purchase->purchasePlots()->forceDelete();

                                // Delete payment plans and payments
                                if ($purchase->paymentPlan) {
                                    $purchase->paymentPlan->payments()->forceDelete();
                                    $purchase->paymentPlan->forceDelete();
                                }

                                // Delete direct payments
                                $purchase->payments()->forceDelete();

                                // Delete PBO sales
                                if ($purchase->pboSale) {
                                    $purchase->pboSale->forceDelete();
                                }

                                // Delete client folders
                                if ($purchase->clientFolder) {
                                    // Delete documents in folder
                                    $purchase->clientFolder->documents()->forceDelete();
                                    $purchase->clientFolder->forceDelete();
                                }

                                // Finally delete purchase
                                $purchase->forceDelete();
                            }

                            // Delete related plots
                            foreach ($record->plots as $plot) {
                                $plot->forceDelete();
                            }

                            // Delete plot types
                            foreach ($record->plotTypes as $plotType) {
                                $plotType->forceDelete();
                            }

                            // Delete other related records
                            $record->promos()->forceDelete();
                            $record->promoCodes()->forceDelete();
                            $record->inspections()->forceDelete();

                            // Finally delete the estate
                            $record->forceDelete();

                            DB::commit();

                            Notification::make()
                                ->title('Success')
                                ->body('Estate deleted successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Error')
                                ->body('Failed to delete estate: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })

                    // Tables\Actions\Action::make('plots')
                    //     ->label('View Plots')
                    //     ->icon('heroicon-o-map')
                    //     ->url(fn (Estate $record): string => route('filament.admin.resources.plots.index', ['estate' => $record->id]))
                    //     ->openUrlInNewTab(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                    ->action(function (Collection $records): void {
                        try {
                            DB::beginTransaction();

                            $records->each(function (Model $record): void {
                                // Handle purchases first
                                foreach ($record->purchases as $purchase) {
                                    // Delete purchase plots
                                    $purchase->purchasePlots()->forceDelete();

                                    // Delete payment plans and payments
                                    if ($purchase->paymentPlan) {
                                        $purchase->paymentPlan->payments()->forceDelete();
                                        $purchase->paymentPlan->forceDelete();
                                    }

                                    // Delete direct payments
                                    $purchase->payments()->forceDelete();

                                    // Delete PBO sales
                                    if ($purchase->pboSale) {
                                        $purchase->pboSale->forceDelete();
                                    }

                                    // Delete client folders
                                    if ($purchase->clientFolder) {
                                        // Delete documents in folder
                                        $purchase->clientFolder->documents()->forceDelete();
                                        $purchase->clientFolder->forceDelete();
                                    }

                                    // Finally delete purchase
                                    $purchase->forceDelete();
                                }

                                // Delete related plots
                                foreach ($record->plots as $plot) {
                                    $plot->forceDelete();
                                }

                                // Delete plot types
                                foreach ($record->plotTypes as $plotType) {
                                    $plotType->forceDelete();
                                }

                                // Delete other related records
                                $record->promos()->forceDelete();
                                $record->promoCodes()->forceDelete();
                                $record->inspections()->forceDelete();

                                // Finally delete the estate
                                $record->forceDelete();
                            });

                            DB::commit();

                            Notification::make()
                                ->title('Success')
                                ->body('Estates deleted successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Error')
                                ->body('Failed to delete estates: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Estate Overview')
                    ->schema([
                        InfolistGrid::make(3)
                            ->schema([
                                SpatieMediaLibraryImageEntry::make('featured_image')
                                    ->label('Featured Image')
                                    ->collection('featured_image')
                                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=111827'),

                                InfolistGroup::make([
                                    TextEntry::make('name')
                                        ->label('Estate Name')
                                        ->weight(FontWeight::Bold)
                                        ->size(TextEntry\TextEntrySize::Large),

                                    TextEntry::make('location.name')
                                        ->label('Location')
                                        ->icon('heroicon-o-map-pin'),

                                    TextEntry::make('address')
                                        ->label('Full Address')
                                        ->icon('heroicon-o-home-modern'),

                                    TextEntry::make('total_area')
                                        ->label('Land Area')
                                        ->icon('heroicon-o-square-2-stack')
                                        ->suffix(' sqm'),

                                    TextEntry::make('status')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'active' => 'success',
                                            'inactive' => 'danger',
                                            'development' => 'warning',
                                            'completed' => 'info',
                                            default => 'gray',
                                        }),
                                ])
                                ->columnSpan(2),
                            ]),

                        InfolistGrid::make(2)
                            ->schema([
                                TextEntry::make('description')
                                    ->label('Description')
                                    ->markdown()
                                    ->columnSpan(2),

                                TextEntry::make('manager.name')
                                    ->label('Estate Manager')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('plots_count')
                                ->state(function (Estate $record): int {
                                    return $record->plots()->count();
                                })
                                ->label('Total Plots')
                                ->icon('heroicon-o-squares-2x2'),

                            TextEntry::make('commercial_plot_premium_percentage')
                                ->label('Commercial Plot Premium')
                                ->icon('heroicon-o-currency-dollar')
                                ->suffix('%'),

                            TextEntry::make('corner_plot_premium_percentage')
                                ->label('Corner Plot Premium')
                                ->icon('heroicon-o-currency-dollar')
                                ->suffix('%'),
                        ]),
                    ])
                    ->collapsible(),

                InfolistSection::make('Plot Types')
                    ->schema([
                        TextEntry::make('plotTypes')
                            ->label('Plot Types')
                            ->state(function (Estate $record): string {
                                $plotTypes = $record->plotTypes;
                                if ($plotTypes->isEmpty()) {
                                    return 'No plot types defined';
                                }

                                $output = "";
                                foreach ($plotTypes as $type) {
                                    $output .= "### {$type->name}\n";
                                    $output .= "- **Size:** {$type->size_sqm} sqm\n";
                                    $output .= "- **Outright Price:** ₦" . number_format($type->outright_price) . "\n";
                                    $output .= "- **6 Months Price:** ₦" . number_format($type->six_month_price) . "\n";
                                    $output .= "- **12 Months Price:** ₦" . number_format($type->twelve_month_price) . "\n";
                                    $output .= "- **Status:** " . ($type->is_active ? 'Active' : 'Inactive') . "\n\n";

                                    // Add count of plots of this type
                                    $plotsCount = $record->plots()->where('estate_plot_type_id', $type->id)->count();
                                    $output .= "- **Plots Created:** {$plotsCount}\n\n";
                                }

                                return $output;
                            })
                            ->markdown(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                InfolistSection::make('Plots Summary')
                    ->schema([
                        TextEntry::make('plots_summary')
                            ->label('Estate Plots')
                            ->state(function (Estate $record): string {
                                $plots = $record->plots;
                                if ($plots->isEmpty()) {
                                    return 'No plots created for this estate';
                                }

                                // Get counts by plot type
                                $plotTypeStats = $record->plots()
                                    ->selectRaw('estate_plot_type_id, COUNT(*) as count')
                                    ->groupBy('estate_plot_type_id')
                                    ->get();

                                $output = "### Plot Distribution\n\n";

                                foreach ($plotTypeStats as $stat) {
                                    $plotType = \App\Models\EstatePlotType::find($stat->estate_plot_type_id);
                                    if ($plotType) {
                                        $output .= "- **{$plotType->name}:** {$stat->count} plots\n";
                                    }
                                }

                                // Get counts by status
                                $statusStats = $record->plots()
                                    ->selectRaw('status, COUNT(*) as count')
                                    ->groupBy('status')
                                    ->get();

                                $output .= "\n### Plot Status\n\n";

                                foreach ($statusStats as $stat) {
                                    $statusLabel = ucfirst($stat->status);
                                    $output .= "- **{$statusLabel}:** {$stat->count} plots\n";
                                }

                                $commercialCount = $record->plots()->where('is_commercial', true)->count();
                                $cornerCount = $record->plots()->where('is_corner', true)->count();

                                $output .= "\n### Special Plots\n\n";
                                $output .= "- **Commercial Plots:** {$commercialCount}\n";
                                $output .= "- **Corner Plots:** {$cornerCount}\n";

                                return $output;
                            })
                            ->markdown(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                InfolistSection::make('Promotions')
                    ->schema([
                        TextEntry::make('promos')
                            ->label('Active Promotions')
                            ->state(function (Estate $record): string {
                                $promos = $record->promos()->where('is_active', true)->get();
                                if ($promos->isEmpty()) {
                                    return 'No active promotions';
                                }

                                $output = "";
                                foreach ($promos as $promo) {
                                    $output .= "### {$promo->name}\n";
                                    $output .= "{$promo->description}\n\n";
                                    $output .= "- **Buy:** {$promo->buy_quantity} plots\n";
                                    $output .= "- **Get Free:** {$promo->free_quantity} plots\n";
                                    $output .= "- **Valid From:** " . $promo->valid_from->format('M d, Y') . "\n";
                                    if ($promo->valid_to) {
                                        $output .= "- **Valid Until:** " . $promo->valid_to->format('M d, Y') . "\n";
                                    } else {
                                        $output .= "- **Valid Until:** No end date\n";
                                    }
                                    $output .= "\n";
                                }

                                return $output;
                            })
                            ->markdown(),

                        TextEntry::make('promo_codes')
                            ->label('Promo Codes')
                            ->state(function (Estate $record): string {
                                $promoCodes = $record->promoCodes()->where('status', 'active')->get();
                                if ($promoCodes->isEmpty()) {
                                    return 'No active promo codes';
                                }

                                $output = "";
                                foreach ($promoCodes as $code) {
                                    $output .= "### {$code->code}\n";
                                    $discountType = $code->discount_type === 'percentage' ? '%' : '₦';
                                    $output .= "- **Discount:** " . $code->discount_amount . ($code->discount_type === 'percentage' ? '%' : ' ₦') . "\n";
                                    $output .= "- **Valid Until:** " . $code->valid_until->format('M d, Y') . "\n";
                                    if ($code->usage_limit) {
                                        $output .= "- **Usage Limit:** {$code->usage_limit}\n";
                                        $output .= "- **Times Used:** {$code->times_used}\n";
                                    } else {
                                        $output .= "- **Usage Limit:** Unlimited\n";
                                    }
                                    $output .= "\n";
                                }

                                return $output;
                            })
                            ->markdown(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                InfolistSection::make('Estate Media')
                    ->schema([
                        TextEntry::make('estate_media')
                            ->label('Estate Images')
                            ->state(function (Estate $record): string {
                                $media = $record->getMedia('estate_images');
                                if ($media->isEmpty()) {
                                    return 'No estate images uploaded';
                                }

                                return 'This estate has ' . $media->count() . ' images.';
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(),

                InfolistSection::make('Additional Information')
                    ->schema([
                        TextEntry::make('faq')
                            ->label('FAQ')
                            ->state(function (Estate $record): string {
                                $faq = $record->faq;
                                if (!$faq || empty($faq)) {
                                    return 'No FAQ available';
                                }

                                $output = "";
                                foreach ($faq as $item) {
                                    $output .= "### Q: {$item['question']}\n";
                                    $output .= "A: {$item['answer']}\n\n";
                                }

                                return $output;
                            })
                            ->markdown(),

                        TextEntry::make('terms')
                            ->label('Terms & Conditions')
                            ->state(function (Estate $record): string {
                                $terms = $record->terms;
                                if (!$terms || empty($terms)) {
                                    return 'No terms and conditions available';
                                }

                                $output = "";
                                foreach ($terms as $item) {
                                    $output .= "### {$item['title']}\n";
                                    $output .= "{$item['content']}\n\n";
                                }

                                return $output;
                            })
                            ->markdown(),

                        TextEntry::make('refund_policy')
                            ->label('Refund Policy')
                            ->state(function (Estate $record): string {
                                $refundPolicy = $record->refund_policy;
                                if (!$refundPolicy || empty($refundPolicy)) {
                                    return 'No refund policy available';
                                }

                                $output = "";
                                foreach ($refundPolicy as $item) {
                                    $output .= "### {$item['title']}\n";
                                    $output .= "{$item['content']}\n\n";
                                }

                                return $output;
                            })
                            ->markdown(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\PlotsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEstates::route('/'),
            'create' => Pages\CreateEstate::route('/create'),
            // 'view' => Pages\ViewEstate::route('/{record}'),
            'edit' => Pages\EditEstate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
