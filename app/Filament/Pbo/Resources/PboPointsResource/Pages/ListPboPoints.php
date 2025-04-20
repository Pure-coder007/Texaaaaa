<?php

namespace App\Filament\Pbo\Resources\PboPointsResource\Pages;

use App\Filament\Pbo\Resources\PboPointsResource;
use App\Filament\Pbo\Resources\PboPointsResource\Widgets\PointsSummaryWidget;
use App\Filament\Pbo\Resources\PboPointsResource\Widgets\ReferralShareWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPboPoints extends ListRecords
{
    protected static string $resource = PboPointsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PointsSummaryWidget::class,
            ReferralShareWidget::class,
        ];
    }
}
