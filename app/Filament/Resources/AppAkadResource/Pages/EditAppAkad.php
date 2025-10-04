<?php

namespace App\Filament\Resources\AppAkadResource\Pages;

use App\Filament\Resources\AppAkadResource;
use App\Models\AppVerifikator;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditAppAkad extends EditRecord
{
    protected static string $resource = AppAkadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            // Simpan data asli sebelum update
            $originalKeputusan = $record->keputusan;

            // Update record dengan data baru
            $record->update($data);

            // Cek apakah keputusan berubah menjadi disetujui
            if ($originalKeputusan !== 'disetujui' && isset($data['keputusan']) && $data['keputusan'] === 'disetujui') {
                // Optimisasi: gunakan exists() untuk cek lebih cepat
                $hasExistingVerifikator = AppVerifikator::where('pemohon_id', $record->pemohon_id)
                    ->where('keputusan', 'disetujui')
                    ->exists();

                // Hanya simpan jika belum ada record verifikator dengan status disetujui
                if (!$hasExistingVerifikator) {
                    AppVerifikator::create([
                        'pemohon_id' => $record->pemohon_id,
                        'keputusan' => $data['keputusan'],
                        'catatan' => $data['catatan'] ?? '',
                        'created_at' => now(),
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            return $record;
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Error in handleRecordUpdate: ' . $e->getMessage());

            // Tetap return record meskipun ada error di verifikator
            return $record;
        }
    }
}
