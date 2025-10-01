<?php

namespace App\Filament\Resources\AppAkadResource\Pages;

use App\Filament\Resources\AppAkadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppAkads extends ListRecords
{
    protected static string $resource = AppAkadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
