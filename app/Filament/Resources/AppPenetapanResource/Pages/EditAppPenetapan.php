<?php

namespace App\Filament\Resources\AppPenetapanResource\Pages;

use App\Filament\Resources\AppPenetapanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppPenetapan extends EditRecord
{
    protected static string $resource = AppPenetapanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
