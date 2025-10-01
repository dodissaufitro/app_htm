<?php

namespace App\Filament\Resources\DataHunianResource\Pages;

use App\Filament\Resources\DataHunianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataHunian extends EditRecord
{
    protected static string $resource = DataHunianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
