<?php

namespace App\Filament\Resources\AppPenetapanResource\Pages;

use App\Filament\Resources\AppPenetapanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppPenetapans extends ListRecords
{
    protected static string $resource = AppPenetapanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
