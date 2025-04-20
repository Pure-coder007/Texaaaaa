<?php

namespace App\Filament\Resources\EstateResource\Pages;

use App\Filament\Resources\EstateResource;
use App\Filament\Resources\EstateResource\Widgets\EstatePlotMetrics;
use App\Filament\Resources\EstateResource\Widgets\EstateStatsOverview;
use App\Services\PlotGenerationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Models\EstatePlotType;
use App\Models\Plot;

class EditEstate extends EditRecord
{
    protected static string $resource = EstateResource::class;

    // Track the plot types before saving
    protected array $previousPlotTypeIds = [];



    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Capture the existing plot types before saving
    protected function beforeSave(): void
    {
        $this->previousPlotTypeIds = $this->record->plotTypes()->pluck('id')->toArray();
    }

    protected function afterSave(): void
    {
        // Handle deleted plot types
        $this->handleDeletedPlotTypes();

        // Generate new plots for current plot types
        $this->generatePlotsForEstate();
    }

    protected function handleDeletedPlotTypes(): void
    {
        $record = $this->record;

        // Get current plot type IDs
        $currentPlotTypeIds = $record->plotTypes()->pluck('id')->toArray();

        // Find deleted plot type IDs
        $deletedPlotTypeIds = array_diff($this->previousPlotTypeIds, $currentPlotTypeIds);

        if (empty($deletedPlotTypeIds)) {
            return; // No plot types were deleted
        }

        // Count the plots that will be affected
        $affectedPlotsCount = Plot::where('estate_id', $record->id)
            ->whereIn('estate_plot_type_id', $deletedPlotTypeIds)
            ->count();

        // Delete the plots associated with deleted plot types
        if ($affectedPlotsCount > 0) {
            Plot::where('estate_id', $record->id)
                ->whereIn('estate_plot_type_id', $deletedPlotTypeIds)
                ->delete();

            Notification::make()
                ->title("{$affectedPlotsCount} plots from deleted plot types were removed")
                ->warning()
                ->send();
        }
    }

    protected function generatePlotsForEstate(): void
    {
        $record = $this->record;
        $plotTypes = $record->plotTypes;

        // Collect data about what changed
        $plotTypesWithChanges = [];

        foreach ($plotTypes as $plotType) {
            // Get current plot count for this type
            $existingPlotCount = $record->plots()
                ->where('estate_plot_type_id', $plotType->id)
                ->count();

            // Calculate how many more plots to generate
            $plotsToGenerate = max(0, $plotType->plot_count - $existingPlotCount);

            // Skip if no new plots need to be generated
            if ($plotsToGenerate <= 0) {
                continue;
            }

            $plotTypesWithChanges[] = [
                'plot_type' => $plotType,
                'existing_count' => $existingPlotCount,
                'plots_to_generate' => $plotsToGenerate,
            ];
        }

        if (empty($plotTypesWithChanges)) {
            Notification::make()
                ->title('No new plots to generate')
                ->info()
                ->send();
            return;
        }

        // Generate new plots where needed
        $plotGenerator = app(PlotGenerationService::class);
        $totalGenerated = 0;

        foreach ($plotTypesWithChanges as $change) {
            $generatedCount = $plotGenerator->generatePlotsForPlotType(
                $change['plot_type'],
                $change['existing_count'],
                $change['plots_to_generate']
            );

            $totalGenerated += $generatedCount;
        }

        Notification::make()
            ->title($totalGenerated > 0 ?
                    "{$totalGenerated} new plots generated successfully" :
                    "No new plots were generated")
            ->success()
            ->send();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EstateStatsOverview::class,
        ];
    }


}