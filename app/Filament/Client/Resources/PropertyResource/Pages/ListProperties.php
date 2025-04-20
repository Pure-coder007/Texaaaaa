<?php

namespace App\Filament\Client\Resources\PropertyResource\Pages;

use App\Filament\Client\Resources\PropertyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProperties extends ListRecords
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
           
        ];
    }
}
