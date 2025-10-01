<?php

namespace App\Filament\Resources\PersetujuanResource\Pages;

use App\Filament\Resources\PersetujuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPersetujuan extends EditRecord
{
    protected static string $resource = PersetujuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
