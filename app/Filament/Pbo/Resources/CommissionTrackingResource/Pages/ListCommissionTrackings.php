<?php

namespace App\Filament\Pbo\Resources\CommissionTrackingResource\Pages;

use App\Filament\Pbo\Resources\CommissionTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommissionTrackings extends ListRecords
{
    protected static string $resource = CommissionTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
           
        ];
    }
}
