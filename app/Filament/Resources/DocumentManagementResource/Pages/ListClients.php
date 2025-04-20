<?php

namespace App\Filament\Resources\DocumentManagementResource\Pages;

use App\Filament\Resources\DocumentManagementResource;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = DocumentManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}