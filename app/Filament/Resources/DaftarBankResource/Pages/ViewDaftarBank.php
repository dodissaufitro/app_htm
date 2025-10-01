<?php

namespace App\Filament\Resources\DaftarBankResource\Pages;

use App\Filament\Resources\DaftarBankResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDaftarBank extends ViewRecord
{
    protected static string $resource = DaftarBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
