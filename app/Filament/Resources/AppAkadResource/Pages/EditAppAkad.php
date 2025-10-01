<?php

namespace App\Filament\Resources\AppAkadResource\Pages;

use App\Filament\Resources\AppAkadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppAkad extends EditRecord
{
    protected static string $resource = AppAkadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
