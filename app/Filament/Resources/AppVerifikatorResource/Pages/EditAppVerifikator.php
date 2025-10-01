<?php

namespace App\Filament\Resources\AppVerifikatorResource\Pages;

use App\Filament\Resources\AppVerifikatorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppVerifikator extends EditRecord
{
    protected static string $resource = AppVerifikatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
