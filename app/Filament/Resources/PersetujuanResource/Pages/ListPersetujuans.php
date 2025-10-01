<?php

namespace App\Filament\Resources\PersetujuanResource\Pages;

use App\Filament\Resources\PersetujuanResource;
use Filament\Resources\Pages\ListRecords;

class ListPersetujuans extends ListRecords
{
    protected static string $resource = PersetujuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action since this is filtered data
        ];
    }
}
