<?php

namespace App\Filament\Client\Resources\MyDocumentsResource\Pages;

use App\Filament\Client\Resources\MyDocumentsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMyDocuments extends CreateRecord
{
    protected static string $resource = MyDocumentsResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
