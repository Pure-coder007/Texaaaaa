<?php

namespace App\Filament\Resources\PboLevelResource\Pages;

use App\Filament\Resources\PboLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePboLevel extends CreateRecord
{
    protected static string $resource = PboLevelResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
