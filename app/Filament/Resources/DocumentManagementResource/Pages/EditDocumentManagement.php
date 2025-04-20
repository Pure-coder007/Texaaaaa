<?php

namespace App\Filament\Resources\DocumentManagementResource\Pages;

use App\Filament\Resources\DocumentManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentManagement extends EditRecord
{
    protected static string $resource = DocumentManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
