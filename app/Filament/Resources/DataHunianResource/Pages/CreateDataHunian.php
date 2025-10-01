<?php

namespace App\Filament\Resources\DataHunianResource\Pages;

use App\Filament\Resources\DataHunianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDataHunian extends CreateRecord
{
    protected static string $resource = DataHunianResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
