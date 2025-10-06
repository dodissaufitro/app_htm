<?php

namespace App\Filament\Resources\AppDeveloperResource\Pages;

use App\Filament\Resources\AppDeveloperResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppDevelopers extends ListRecords
{
    protected static string $resource = AppDeveloperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
