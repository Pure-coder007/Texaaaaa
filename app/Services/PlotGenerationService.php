<?php

namespace App\Services;

use App\Models\Estate;
use App\Models\EstatePlotType;
use App\Models\Plot;

class PlotGenerationService
{
    /**
     * Generate plots for an estate, only adding new plots where necessary
     */
    public function generatePlotsForEstate(Estate $estate): int
    {
        // Get all plot types for this estate
        $plotTypes = $estate->plotTypes;
        $totalGenerated = 0;

        foreach ($plotTypes as $plotType) {
            // Skip if plot count is 0 or not set
            $plotCount = $plotType->plot_count ?? 0;
            if ($plotCount <= 0) {
                continue;
            }

            // Get existing plots count for this type
            $existingPlotCount = $estate->plots()
                ->where('estate_plot_type_id', $plotType->id)
                ->count();

            // Calculate how many more plots to generate
            $plotsToGenerate = max(0, $plotCount - $existingPlotCount);

            // Skip if no new plots need to be generated
            if ($plotsToGenerate <= 0) {
                continue;
            }

            // Generate the additional plots
            $generatedCount = $this->generatePlotsForPlotType(
                $plotType,
                $existingPlotCount,
                $plotsToGenerate
            );

            $totalGenerated += $generatedCount;
        }

        return $totalGenerated;
    }

    /**
     * Generate plots for a specific plot type
     */
    public function generatePlotsForPlotType(
        EstatePlotType $plotType,
        int $existingCount = 0,
        int $countToGenerate = null
    ): int {
        $estate = $plotType->estate;

        // Determine plot count to generate
        if ($countToGenerate === null) {
            $totalDesired = $plotType->plot_count ?? 0;
            $countToGenerate = max(0, $totalDesired - $existingCount);
        }

        if ($countToGenerate <= 0) {
            return 0;
        }

        // Extract plot type properties from name
        $isCommercial = false;
        $isCorner = false;

        if (strpos($plotType->name, 'Commercial') !== false) {
            $isCommercial = true;
        }

        if (strpos($plotType->name, 'Corner') !== false) {
            $isCorner = true;
        }

        // Generate plots
        $createdCount = 0;
        for ($i = 0; $i < $countToGenerate; $i++) {
            Plot::create([
                'estate_id' => $estate->id,
                'estate_plot_type_id' => $plotType->id,
                'area' => $plotType->size_sqm,
                'dimensions' => null,
                'price' => $plotType->outright_price,
                'status' => 'available',
                'is_commercial' => $isCommercial,
                'is_corner' => $isCorner,
            ]);

            $createdCount++;
        }

        // Mark plot type as having plots generated
        $plotType->update(['plots_generated' => true]);

        return $createdCount;
    }

    /**
     * Regenerate all plots for a specific plot type (deletes existing and creates new)
     */
    public function regeneratePlotsForPlotType(EstatePlotType $plotType): int
    {
        // Delete existing plots of this type
        $deleted = Plot::where('estate_plot_type_id', $plotType->id)->delete();

        // Generate new plots
        return $this->generatePlotsForPlotType($plotType, 0, $plotType->plot_count);
    }
}