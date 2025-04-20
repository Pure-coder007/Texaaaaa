<?php

namespace App\Filament\Resources\EstateResource\Pages;

use App\Filament\Resources\EstateResource;
use App\Services\PlotGenerationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEstate extends CreateRecord
{
    protected static string $resource = EstateResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {

        $record = $this->record;

        $plotGenerator = app(PlotGenerationService::class);
        $plotGenerator->generatePlotsForEstate($record);

        Notification::make()
            ->title('Plots generated automatically')
            ->success()
            ->send();
    }
}
