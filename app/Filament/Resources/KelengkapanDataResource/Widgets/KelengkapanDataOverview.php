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
            // Ensure allowed_status is an array
            $allowedStatus = $user->allowed_status;
            if (is_string($allowedStatus)) {
                $allowedStatus = json_decode($allowedStatus, true);
            }
            if (is_array($allowedStatus) && !empty($allowedStatus)) {
                $baseQuery->whereIn('status_permohonan', $allowedStatus);
            }
        }

        // Get allowed statuses
        $statusesQuery = Status::orderBy('urut');
        if (!empty($user->allowed_status)) {
            // Ensure allowed_status is an array
            $allowedStatus = $user->allowed_status;
            if (is_string($allowedStatus)) {
                $allowedStatus = json_decode($allowedStatus, true);
            }
            if (is_array($allowedStatus) && !empty($allowedStatus)) {
                $statusesQuery->whereIn('kode', $allowedStatus);
            }
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
                '-1' => 'danger',      // Tidak lolos Verifikasi
                '0' => 'warning',      // Ditunda Bank
                '1' => 'warning',      // Ditunda Verifikator
                '2' => 'info',         // Approval Pengembang/ Developer
                '3' => 'danger',       // Ditolak
                '4' => 'gray',         // Dibatalkan
                '5' => 'info',         // Administrasi Bank
                '6' => 'warning',      // Ditunda Developer
                '8' => 'danger',       // Tidak lolos analisa perbankan
                '9' => 'success',      // Bank
                '10' => 'success',     // Akad Kredit
                '11' => 'success',     // BAST
                '12' => 'success',     // Selesai
                '15' => 'info',        // Verifikasi Dokumen Pendaftaran
                '16' => 'info',        // Tahap Survey
                '17' => 'info',        // Penetapan
                '18' => 'gray',        // Pengajuan Dibatalkan
                '19' => 'info',        // Verifikasi Dokumen Pendaftaran
                '20' => 'warning',     // Ditunda Penetapan
                default => 'primary'
            };

            // Get icon for status
            $icon = match ($status->kode) {
                '-1' => 'heroicon-o-x-circle',         // Tidak lolos Verifikasi
                '0' => 'heroicon-o-pause',             // Ditunda Bank
                '1' => 'heroicon-o-pause',             // Ditunda Verifikator
                '2' => 'heroicon-o-check-badge',       // Approval Pengembang/ Developer
                '3' => 'heroicon-o-x-circle',          // Ditolak
                '4' => 'heroicon-o-no-symbol',         // Dibatalkan
                '5' => 'heroicon-o-building-library',  // Administrasi Bank
                '6' => 'heroicon-o-pause',             // Ditunda Developer
                '8' => 'heroicon-o-x-circle',          // Tidak lolos analisa perbankan
                '9' => 'heroicon-o-building-library',  // Bank
                '10' => 'heroicon-o-document-text',    // Akad Kredit
                '11' => 'heroicon-o-check-circle',     // BAST
                '12' => 'heroicon-o-check-circle',     // Selesai
                '15' => 'heroicon-o-document-check',   // Verifikasi Dokumen Pendaftaran
                '16' => 'heroicon-o-map-pin',          // Tahap Survey
                '17' => 'heroicon-o-clipboard-document', // Penetapan
                '18' => 'heroicon-o-no-symbol',        // Pengajuan Dibatalkan
                '19' => 'heroicon-o-document-check',   // Verifikasi Dokumen Pendaftaran
                '20' => 'heroicon-o-pause',            // Ditunda Penetapan
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
                // Ensure allowed_status is an array
                $allowedStatus = $user->allowed_status;
                if (is_string($allowedStatus)) {
                    $allowedStatus = json_decode($allowedStatus, true);
                }
                if (is_array($allowedStatus) && !empty($allowedStatus)) {
                    $query->whereIn('status_permohonan', $allowedStatus);
                }
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
                // Ensure allowed_status is an array
                $allowedStatus = $user->allowed_status;
                if (is_string($allowedStatus)) {
                    $allowedStatus = json_decode($allowedStatus, true);
                }
                if (is_array($allowedStatus) && !empty($allowedStatus)) {
                    $query->whereIn('status_permohonan', $allowedStatus);
                }
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
                // Ensure allowed_status is an array
                $allowedStatus = $user->allowed_status;
                if (is_string($allowedStatus)) {
                    $allowedStatus = json_decode($allowedStatus, true);
                }
                if (is_array($allowedStatus) && !empty($allowedStatus)) {
                    $query->whereIn('status_permohonan', $allowedStatus);
                }
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
