<?php

namespace App\Filament\Pbo\Resources\CommissionTrackingResource\Pages;

use App\Filament\Pbo\Resources\CommissionTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommissionTracking extends EditRecord
{
    protected static string $resource = CommissionTrackingResource::class;

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
