<?php

namespace App\Filament\Resources\PboResource\Pages;

use App\Filament\Resources\PboResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePbo extends CreateRecord
{
    protected static string $resource = PboResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
