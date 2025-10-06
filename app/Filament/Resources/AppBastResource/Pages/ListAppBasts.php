<?php

namespace App\Filament\Resources\AppBastResource\Pages;

use App\Filament\Resources\AppBastResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppBasts extends ListRecords
{
    protected static string $resource = AppBastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
