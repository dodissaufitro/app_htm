<?php

namespace App\Observers;

use App\Models\DataPemohon;
use App\Models\AppVerifikator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DataPemohonObserver
{
    /**
     * Handle the DataPemohon "updated" event.
     *
     * @param  \App\Models\DataPemohon  $dataPemohon
     * @return void
     */
    public function updated(DataPemohon $dataPemohon)
    {
        // Check if status_permohonan was changed
        if ($dataPemohon->isDirty('status_permohonan')) {
            $this->handleStatusChange($dataPemohon);
        }
    }

    /**
     * Handle status change and create/update app_verifikator record
     *
     * @param  \App\Models\DataPemohon  $dataPemohon
     * @return void
     */
    private function handleStatusChange(DataPemohon $dataPemohon)
    {
        try {
            $newStatus = $dataPemohon->status_permohonan;
            $oldStatus = $dataPemohon->getOriginal('status_permohonan');

            Log::info("DataPemohonObserver: Status changed from {$oldStatus} to {$newStatus} for pemohon ID: {$dataPemohon->id}");

            // Map status to keputusan verifikator - sesuai dengan status yang tersedia
            $keputusanMapping = [
                // Status Codes yang ada di sistem
                '-1' => 'ditolak',    // Tidak lolos Verifikasi
                '0' => 'ditunda',     // Ditunda Bank
                '1' => 'ditunda',     // Ditunda Verifikator
                '2' => 'disetujui',   // Approval Pengembang/Developer
                '3' => 'ditolak',     // Ditolak
                '4' => 'ditolak',     // Dibatalkan
                '5' => 'ditunda',     // Administrasi Bank
                '6' => 'ditunda',     // Ditunda Developer
                '8' => 'ditolak',     // Tidak lolos analisa perbankan
                '9' => 'disetujui',   // Bank
                '10' => 'disetujui',  // Akad Kredit
                '11' => 'disetujui',  // BAST
                '12' => 'disetujui',  // Selesai
                '15' => 'ditunda',    // Verifikasi Dokumen Pendaftaran
                '16' => 'ditunda',    // Tahap Survey
                '17' => 'ditunda',    // Penetapan
                '18' => 'ditolak',    // Pengajuan Dibatalkan
                '19' => 'ditunda',    // Verifikasi Dokumen Pendaftaran
                '20' => 'ditunda',    // Ditunda Penetapan

                // Legacy status codes (backward compatibility)
                'DRAFT' => 'ditunda',
                'SUBMITTED' => 'ditunda',
                'UNDER_REVIEW' => 'ditunda',
                'APPROVED' => 'disetujui',
                'REJECTED' => 'ditolak',
                'COMPLETED' => 'disetujui',
                'PROSES' => 'ditunda',
            ];

            $keputusan = $keputusanMapping[$newStatus] ?? 'ditunda';

            // Generate appropriate catatan
            $catatan = $this->generateCatatan($oldStatus, $newStatus, $dataPemohon);

            // Enhanced duplicate prevention logic
            $this->handleVerifikatorRecord($dataPemohon->id, $keputusan, $catatan);
        } catch (\Exception $e) {
            Log::error("DataPemohonObserver: Error handling status change for pemohon ID {$dataPemohon->id}: " . $e->getMessage());
            Log::error("DataPemohonObserver: Exception trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Handle verifikator record creation/update with duplicate prevention
     *
     * @param  int  $pemohonId
     * @param  string  $keputusan
     * @param  string  $catatan
     * @return void
     */
    private function handleVerifikatorRecord(int $pemohonId, string $keputusan, string $catatan): void
    {
        // Start transaction to prevent race conditions
        DB::beginTransaction();

        try {
            // Check for existing records and handle duplicates
            $existingRecords = AppVerifikator::where('pemohon_id', $pemohonId)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($existingRecords->count() > 1) {
                // Clean up duplicates - keep the latest one
                Log::warning("DataPemohonObserver: Found {$existingRecords->count()} duplicate records for pemohon {$pemohonId}, cleaning up...");

                $keepRecord = $existingRecords->first();
                $deleteRecords = $existingRecords->skip(1);

                foreach ($deleteRecords as $record) {
                    Log::info("DataPemohonObserver: Deleting duplicate record ID: {$record->id}");
                    $record->delete();
                }

                $existingVerifikator = $keepRecord;
            } else {
                $existingVerifikator = $existingRecords->first();
            }

            if ($existingVerifikator) {
                // Check if existing record is from more specific source (like AppAkad)
                $isFromSpecificProcess = str_contains($existingVerifikator->catatan, 'akad') ||
                    str_contains($existingVerifikator->catatan, 'bank') ||
                    str_contains($existingVerifikator->catatan, 'penetapan') ||
                    str_contains($existingVerifikator->catatan, 'BAST') ||
                    str_contains($existingVerifikator->catatan, 'kredit');

                if ($isFromSpecificProcess) {
                    Log::info("DataPemohonObserver: Skipping update - existing record appears to be from specific process");
                    DB::commit();
                    return;
                }

                // Update existing record
                $existingVerifikator->update([
                    'keputusan' => $keputusan,
                    'catatan' => $catatan,
                    'created_at' => now(),
                    'created_by' => Auth::id() ?? 1, // fallback to user ID 1 if no auth
                ]);

                Log::info("DataPemohonObserver: Updated existing app_verifikator record ID: {$existingVerifikator->id}");
            } else {
                // Create new record
                $verifikator = AppVerifikator::create([
                    'pemohon_id' => $pemohonId,
                    'keputusan' => $keputusan,
                    'catatan' => $catatan,
                    'created_at' => now(),
                    'created_by' => Auth::id() ?? 1, // fallback to user ID 1 if no auth
                ]);

                Log::info("DataPemohonObserver: Created new app_verifikator record ID: {$verifikator->id}");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("DataPemohonObserver: Error handling verifikator record for pemohon {$pemohonId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate appropriate catatan based on status change
     *
     * @param  string|null  $oldStatus
     * @param  string  $newStatus
     * @param  \App\Models\DataPemohon  $dataPemohon
     * @return string
     */
    private function generateCatatan($oldStatus, $newStatus, DataPemohon $dataPemohon): string
    {
        // Prioritas: gunakan keterangan dari user jika ada, baru fallback ke auto-generated
        if (!empty($dataPemohon->keterangan)) {
            Log::info("DataPemohonObserver: Using user keterangan for pemohon {$dataPemohon->id}");
            return $dataPemohon->keterangan;
        }

        $statusNames = [
            '-1' => 'Tidak lolos Verifikasi',
            '0' => 'Ditunda Bank',
            '1' => 'Ditunda Verifikator',
            '2' => 'Approval Pengembang/Developer',
            '3' => 'Ditolak',
            '4' => 'Dibatalkan',
            '5' => 'Administrasi Bank',
            '6' => 'Ditunda Developer',
            '8' => 'Tidak lolos analisa perbankan',
            '9' => 'Bank',
            '10' => 'Akad Kredit',
            '11' => 'BAST',
            '12' => 'Selesai',
            '15' => 'Verifikasi Dokumen Pendaftaran',
            '16' => 'Tahap Survey',
            '17' => 'Penetapan',
            '18' => 'Pengajuan Dibatalkan',
            '19' => 'Verifikasi Dokumen Pendaftaran',
            '20' => 'Ditunda Penetapan',

            // Legacy status names (backward compatibility)
            'DRAFT' => 'Draft',
            'SUBMITTED' => 'Diajukan',
            'UNDER_REVIEW' => 'Dalam Review',
            'APPROVED' => 'Disetujui',
            'REJECTED' => 'Ditolak',
            'COMPLETED' => 'Selesai',
            'PROSES' => 'Pemohon Baru',
        ];

        $oldStatusName = $statusNames[$oldStatus] ?? $oldStatus ?? 'Unknown';
        $newStatusName = $statusNames[$newStatus] ?? $newStatus;

        $catatan = "Status permohonan berubah dari '{$oldStatusName}' ke '{$newStatusName}' pada " . now()->format('d/m/Y H:i:s');

        // Add user info if available
        $user = Auth::user();
        if ($user) {
            $catatan .= " oleh {$user->name}";
        }

        // Add pemohon info for context
        $catatan .= " untuk pemohon: {$dataPemohon->nama} (ID: {$dataPemohon->id_pendaftaran})";

        return $catatan;
    }
}
