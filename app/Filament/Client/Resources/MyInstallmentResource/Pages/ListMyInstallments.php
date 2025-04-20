<?php

namespace App\Filament\Client\Resources\MyInstallmentResource\Pages;

use App\Filament\Client\Resources\MyInstallmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyInstallments extends ListRecords
{
    protected static string $resource = MyInstallmentResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MyInstallmentResource\Widgets\InstallmentOverview::class,
        ];
    }
}
