<?php

namespace App\Filament\Resources\KelengkapanDataResource\Pages;

use App\Filament\Resources\KelengkapanDataResource;
use App\Filament\Resources\KelengkapanDataResource\Widgets\KelengkapanDataOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListKelengkapanData extends ListRecords
{
    protected static string $resource = KelengkapanDataResource::class;

    public $activeFilter = null;
    public $activeFilterValue = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data Baru'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KelengkapanDataOverview::class,
        ];
    }

    // Remove tabs completely - will be replaced with cards
    public function getTabs(): array
    {
        return [];
    }

    // Apply additional query modifications based on card selections
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Apply card-based filters
        if ($this->activeFilter && $this->activeFilterValue) {
            switch ($this->activeFilter) {
                case 'status':
                    if ($this->activeFilterValue !== 'all') {
                        $query->where('status_permohonan', $this->activeFilterValue);
                    }
                    break;
                case 'bank':
                    $query->where('id_bank', $this->activeFilterValue);
                    break;
                case 'income':
                    if ($this->activeFilterValue === 'high') {
                        $query->where('gaji', '>=', 10000000);
                    }
                    break;
                case 'couple':
                    $query->where('is_couple_dki', true);
                    break;
            }
        }

        return $query;
    }

    // Filter methods called from cards
    public function handleStatusFilter(string $status): void
    {
        $this->activeFilter = 'status';
        $this->activeFilterValue = $status;
        $this->resetTable();
    }

    public function handleBankFilter(string $bank): void
    {
        $this->activeFilter = 'bank';
        $this->activeFilterValue = $bank;
        $this->resetTable();
    }

    public function handleIncomeFilter(string $type): void
    {
        $this->activeFilter = 'income';
        $this->activeFilterValue = $type;
        $this->resetTable();
    }

    public function handleCoupleFilter(bool $hasCouple): void
    {
        $this->activeFilter = 'couple';
        $this->activeFilterValue = $hasCouple;
        $this->resetTable();
    }

    public function clearFilters(): void
    {
        $this->activeFilter = null;
        $this->activeFilterValue = null;
        $this->resetTable();
    }
}
