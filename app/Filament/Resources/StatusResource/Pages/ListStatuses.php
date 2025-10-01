<?php

namespace App\Filament\Resources\StatusResource\Pages;

use App\Filament\Resources\StatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStatuses extends ListRecords
{
    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Status Baru')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Status')
                ->badge($this->getModel()::count()),
            'urutan_awal' => Tab::make('Status Awal')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('urut', '<=', 3))
                ->badge($this->getModel()::where('urut', '<=', 3)->count()),
            'urutan_tengah' => Tab::make('Status Proses')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereBetween('urut', [4, 7]))
                ->badge($this->getModel()::whereBetween('urut', [4, 7])->count()),
            'urutan_akhir' => Tab::make('Status Final')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('urut', '>', 7))
                ->badge($this->getModel()::where('urut', '>', 7)->count()),
        ];
    }
}
