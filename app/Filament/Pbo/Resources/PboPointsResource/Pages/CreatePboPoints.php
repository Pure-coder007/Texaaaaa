<?php

namespace App\Filament\Pbo\Resources\PboPointsResource\Pages;

use App\Filament\Pbo\Resources\PboPointsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePboPoints extends CreateRecord
{
    protected static string $resource = PboPointsResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
