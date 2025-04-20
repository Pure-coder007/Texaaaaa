<?php

namespace App\Filament\Client\Resources\MyInstallmentResource\Pages;

use App\Filament\Client\Resources\MyInstallmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMyInstallment extends CreateRecord
{
    protected static string $resource = MyInstallmentResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
