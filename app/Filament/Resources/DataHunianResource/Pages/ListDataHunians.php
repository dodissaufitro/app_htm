<?php

namespace App\Filament\Resources\DataHunianResource\Pages;

use App\Filament\Resources\DataHunianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataHunians extends ListRecords
{
    protected static string $resource = DataHunianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
