<?php

namespace App\Filament\Resources\AppPenetapanResource\Pages;

use App\Filament\Resources\AppPenetapanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppPenetapan extends ViewRecord
{
    protected static string $resource = AppPenetapanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
