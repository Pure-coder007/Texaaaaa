<?php

namespace App\Filament\Resources\InstallmentResource\Pages;

use App\Filament\Resources\InstallmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInstallment extends CreateRecord
{
    protected static string $resource = InstallmentResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
