<?php

namespace App\Filament\Resources\PersetujuanResource\Pages;

use App\Filament\Resources\PersetujuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPersetujuan extends ViewRecord
{
    protected static string $resource = PersetujuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
