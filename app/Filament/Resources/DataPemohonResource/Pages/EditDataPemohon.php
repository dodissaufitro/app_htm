<?php

namespace App\Filament\Resources\DataPemohonResource\Pages;

use App\Filament\Resources\DataPemohonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataPemohon extends EditRecord
{
    protected static string $resource = DataPemohonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
