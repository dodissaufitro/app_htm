<?php

namespace App\Filament\Resources\AppBastResource\Pages;

use App\Filament\Resources\AppBastResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppBast extends EditRecord
{
    protected static string $resource = AppBastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
