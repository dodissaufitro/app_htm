<?php

namespace App\Filament\Resources\AppBankResource\Pages;

use App\Filament\Resources\AppBankResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppBanks extends ListRecords
{
    protected static string $resource = AppBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
