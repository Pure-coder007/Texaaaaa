<?php

namespace App\Filament\Client\Resources\PropertyResource\Pages;

use App\Filament\Client\Resources\PropertyResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProperty extends ViewRecord
{
    protected static string $resource = PropertyResource::class;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
