<?php

namespace App\Filament\Resources\DataPemohonResource\Pages;

use App\Filament\Resources\DataPemohonResource;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListDataPemohons extends ListRecords
{
    protected static string $resource = DataPemohonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();

        // Get all statuses ordered by urut
        $statusesQuery = Status::orderBy('urut');

        // If user has restricted access, only get allowed statuses
        if (!empty($user->allowed_status)) {
            $statusesQuery->whereIn('kode', $user->allowed_status);
        }

        $statuses = $statusesQuery->get();

        $tabs = [];

        // Only show "Semua" tab if user can see multiple statuses
        if ($statuses->count() > 1) {
            // Calculate total count respecting user access control
            $totalQuery = $this->getModel()::query();
            if (!empty($user->allowed_status)) {
                $totalQuery->whereIn('status_permohonan', $user->allowed_status);
            }

            $tabs['semua'] = Tab::make('Semua')
                ->badge($totalQuery->count());
        }

        // Add tabs for each allowed status
        foreach ($statuses as $status) {
            $tabs[$status->kode] = Tab::make($status->nama_status)
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status_permohonan', $status->kode))
                ->badge($this->getModel()::where('status_permohonan', $status->kode)->count());
        }

        return $tabs;
    }
}
