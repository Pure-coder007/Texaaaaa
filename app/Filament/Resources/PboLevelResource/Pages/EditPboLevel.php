<?php

namespace App\Filament\Resources\PboLevelResource\Pages;

use App\Filament\Resources\PboLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPboLevel extends EditRecord
{
    protected static string $resource = PboLevelResource::class;

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
