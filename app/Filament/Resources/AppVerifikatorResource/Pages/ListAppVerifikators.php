<?php

namespace App\Filament\Resources\AppVerifikatorResource\Pages;

use App\Filament\Resources\AppVerifikatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppVerifikators extends ListRecords
{
    protected static string $resource = AppVerifikatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
