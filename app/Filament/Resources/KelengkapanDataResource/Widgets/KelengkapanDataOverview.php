<?php

namespace App\Filament\Resources\KelengkapanDataResource\Widgets;

use App\Models\Status;
use App\Models\DataPemohon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class KelengkapanDataOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        // Get base query with user access control
        $baseQuery = DataPemohon::query();
        if (!empty($user->allowed_status)) {
            $baseQuery->whereIn('status_permohonan', $user->allowed_status);
        }

        // Get allowed statuses
        $statusesQuery = Status::orderBy('urut');
        if (!empty($user->allowed_status)) {
            $statusesQuery->whereIn('kode', $user->allowed_status);
        }
        $statuses = $statusesQuery->get();

        $stats = [];

        // Total Data Card
        $totalCount = $baseQuery->count();
        $stats[] = Stat::make('Total Data', $totalCount)
            ->description('Total semua data pemohon')
            ->descriptionIcon('heroicon-o-users')
            ->color('primary')
            ->extraAttributes([
                'class' => 'cursor-pointer hover:bg-gray-50 transition-colors',
                'wire:click' => 'handleStatusFilter("all")'
            ]);

        // Individual Status Cards
        foreach ($statuses as $status) {
            $count = DataPemohon::where('status_permohonan', $status->kode)->count();

            // Get status color
            $color = match ($status->kode) {
                'DRAFT' => 'gray',
                'PROSES' => 'warning',
                'DITUNDA UPDP' => 'danger',
                'LOLOS UPDP' => 'info',
                'SUBMITTED' => 'warning',
                'UNDER_REVIEW' => 'info',
                'APPROVED' => 'success',
                'REJECTED' => 'danger',
                'COMPLETED' => 'success',
                default => 'primary'
            };

            // Get icon for status
            $icon = match ($status->kode) {
                'DRAFT' => 'heroicon-o-document',
                'PROSES' => 'heroicon-o-clock',
                'DITUNDA UPDP' => 'heroicon-o-pause',
                'LOLOS UPDP' => 'heroicon-o-check-circle',
                'SUBMITTED' => 'heroicon-o-paper-airplane',
                'UNDER_REVIEW' => 'heroicon-o-eye',
                'APPROVED' => 'heroicon-o-check-badge',
                'REJECTED' => 'heroicon-o-x-circle',
                'COMPLETED' => 'heroicon-o-check-circle',
                default => 'heroicon-o-clipboard-document'
            };

            $stats[] = Stat::make($status->nama_status, $count)
                ->description($status->keterangan ?? 'Status ' . $status->nama_status)
                ->descriptionIcon($icon)
                ->color($color)
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 transition-colors',
                    'wire:click' => 'handleStatusFilter("' . $status->kode . '")'
                ]);
        }

        // Additional report cards

        // Data by Bank
        $bankStats = DataPemohon::selectRaw('id_bank, COUNT(*) as count')
            ->when(!empty($user->allowed_status), function ($query) use ($user) {
                $query->whereIn('status_permohonan', $user->allowed_status);
            })
            ->whereNotNull('id_bank')
            ->groupBy('id_bank')
            ->with('bank')
            ->get();

        if ($bankStats->count() > 0) {
            $topBank = $bankStats->sortByDesc('count')->first();
            $stats[] = Stat::make('Bank Terbanyak', $topBank->bank->nama_bank ?? 'N/A')
                ->description($topBank->count . ' pemohon')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 transition-colors',
                    'wire:click' => 'handleBankFilter("' . $topBank->id_bank . '")'
                ]);
        }

        // Data by Income Range
        $highIncomeCount = DataPemohon::where('gaji', '>=', 10000000)
            ->when(!empty($user->allowed_status), function ($query) use ($user) {
                $query->whereIn('status_permohonan', $user->allowed_status);
            })
            ->count();

        $stats[] = Stat::make('Gaji ≥ 10 Juta', $highIncomeCount)
            ->description('Pemohon dengan gaji tinggi')
            ->descriptionIcon('heroicon-o-banknotes')
            ->color('success')
            ->extraAttributes([
                'class' => 'cursor-pointer hover:bg-gray-50 transition-colors',
                'wire:click' => 'handleIncomeFilter("high")'
            ]);

        // Couple data
        $coupleCount = DataPemohon::where('is_couple_dki', true)
            ->when(!empty($user->allowed_status), function ($query) use ($user) {
                $query->whereIn('status_permohonan', $user->allowed_status);
            })
            ->count();

        $stats[] = Stat::make('Pasangan DKI', $coupleCount)
            ->description('Pemohon dengan pasangan DKI')
            ->descriptionIcon('heroicon-o-heart')
            ->color('rose')
            ->extraAttributes([
                'class' => 'cursor-pointer hover:bg-gray-50 transition-colors',
                'wire:click' => 'handleCoupleFilter(true)'
            ]);

        return $stats;
    }

    protected function getColumns(): int
    {
        return 4; // 4 columns for responsive grid
    }
}
