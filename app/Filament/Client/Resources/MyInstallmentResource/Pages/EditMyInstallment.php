<?php

namespace App\Filament\Client\Resources\MyInstallmentResource\Pages;

use App\Filament\Client\Resources\MyInstallmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyInstallment extends EditRecord
{
    protected static string $resource = MyInstallmentResource::class;

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
