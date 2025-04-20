<?php

namespace App\Filament\Resources\PboLevelResource\Pages;

use App\Filament\Resources\PboLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPboLevels extends ListRecords
{
    protected static string $resource = PboLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
