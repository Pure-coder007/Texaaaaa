<?php

namespace App\Filament\Client\Resources\InspectionResource\Pages;

use App\Filament\Client\Resources\InspectionResource;
use App\Models\Inspection;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInspection extends CreateRecord
{
    protected static string $resource = InspectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['client_id'] = auth()->id();
        $data['status'] = 'pending';
        $data['scheduled_time'] = '10:00:00'; // Fixed time at 10 AM

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        // You can add additional logic here, like sending notifications to admin

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}