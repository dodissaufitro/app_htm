<?php

namespace App\Filament\Resources\AppAkadResource\Pages;

use App\Filament\Resources\AppAkadResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppAkad extends ViewRecord
{
    protected static string $resource = AppAkadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
