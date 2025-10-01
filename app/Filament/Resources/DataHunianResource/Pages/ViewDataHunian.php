<?php

namespace App\Filament\Resources\DataHunianResource\Pages;

use App\Filament\Resources\DataHunianResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDataHunian extends ViewRecord
{
    protected static string $resource = DataHunianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
