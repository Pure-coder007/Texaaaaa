<?php

namespace App\Filament\Resources\DocumentManagementResource\Pages;

use App\Filament\Resources\DocumentManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentManagement extends CreateRecord
{
    protected static string $resource = DocumentManagementResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
