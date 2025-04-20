<?php

namespace App\Filament\Resources\DocumentManagementResource\Pages;

use App\Filament\Resources\DocumentManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocumentManagement extends ListRecords
{
    protected static string $resource = DocumentManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
