<?php

namespace App\Filament\Client\Resources\MyDocumentsResource\Pages;

use App\Filament\Client\Resources\MyDocumentsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyDocuments extends ListRecords
{
    protected static string $resource = MyDocumentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
