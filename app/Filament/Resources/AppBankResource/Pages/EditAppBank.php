<?php

namespace App\Filament\Resources\AppBankResource\Pages;

use App\Filament\Resources\AppBankResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppBank extends EditRecord
{
    protected static string $resource = AppBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
