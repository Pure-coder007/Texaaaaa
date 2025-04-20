<?php

namespace App\Filament\Pbo\Resources\CommissionTrackingResource\Pages;

use App\Filament\Pbo\Resources\CommissionTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCommissionTracking extends CreateRecord
{
    protected static string $resource = CommissionTrackingResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
