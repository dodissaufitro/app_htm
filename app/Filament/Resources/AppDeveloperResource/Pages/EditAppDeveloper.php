<?php

namespace App\Filament\Resources\AppDeveloperResource\Pages;

use App\Filament\Resources\AppDeveloperResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppDeveloper extends EditRecord
{
    protected static string $resource = AppDeveloperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
