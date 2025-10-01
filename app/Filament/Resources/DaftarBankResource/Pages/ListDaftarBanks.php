<?php

namespace App\Filament\Resources\DaftarBankResource\Pages;

use App\Filament\Resources\DaftarBankResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDaftarBanks extends ListRecords
{
    protected static string $resource = DaftarBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Bank Baru')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Bank')
                ->badge($this->getModel()::count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add widgets here for statistics
        ];
    }
}
