<?php

namespace App\Filament\Resources\PersetujuanDeveloperResource\Pages;

use App\Filament\Resources\PersetujuanDeveloperResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePersetujuanDeveloper extends CreateRecord
{
    protected static string $resource = PersetujuanDeveloperResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
