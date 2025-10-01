<?php

namespace App\Filament\Resources\KelengkapanDataResource\Pages;

use App\Filament\Resources\KelengkapanDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKelengkapanData extends ViewRecord
{
    protected static string $resource = KelengkapanDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
