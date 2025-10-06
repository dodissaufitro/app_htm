<?php

namespace App\Filament\Resources\AppBankResource\Pages;

use App\Filament\Resources\AppBankResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppBank extends ViewRecord
{
    protected static string $resource = AppBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
