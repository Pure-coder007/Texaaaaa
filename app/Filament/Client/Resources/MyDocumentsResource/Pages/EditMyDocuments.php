<?php

namespace App\Filament\Client\Resources\MyDocumentsResource\Pages;

use App\Filament\Client\Resources\MyDocumentsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyDocuments extends EditRecord
{
    protected static string $resource = MyDocumentsResource::class;

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
