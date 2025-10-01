<?php

namespace App\Filament\Resources\AppVerifikatorResource\Pages;

use App\Filament\Resources\AppVerifikatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppVerifikator extends ViewRecord
{
    protected static string $resource = AppVerifikatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
