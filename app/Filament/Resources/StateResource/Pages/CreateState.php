<?php

namespace App\Filament\Resources\StateResource\Pages;

use App\Filament\Resources\StateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateState extends CreateRecord
{
    protected static string $resource = StateResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
