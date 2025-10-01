<?php

namespace App\Filament\Resources\DataPemohonResource\Pages;

use App\Filament\Resources\DataPemohonResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDataPemohon extends ViewRecord
{
    protected static string $resource = DataPemohonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
