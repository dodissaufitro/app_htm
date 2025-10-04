<?php

namespace App\Filament\Resources\AppVerifikatorResource\Pages;

use App\Filament\Resources\AppVerifikatorResource;
use App\Models\AppVerifikator;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditAppVerifikator extends EditRecord
{
    protected static string $resource = AppVerifikatorResource::class;

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
            DB::beginTransaction();

            // CRITICAL: Log data yang masuk dan kill semua kemungkinan 'status' field
            Log::info('EditAppVerifikator handleRecordUpdate - Raw data received: ', $data);

            // AGGRESSIVE filtering - hapus semua kemungkinan variasi status field
            $allStatusVariations = ['status', 'Status', 'STATUS', 'keputusan_status', 'pemohon_status'];
            foreach ($allStatusVariations as $field) {
                if (isset($data[$field])) {
                    Log::warning("FOUND AND REMOVING dangerous field: {$field} with value: " . json_encode($data[$field]));
                    unset($data[$field]);
                }
            }

            // Pisahkan data dan filter dengan sangat ketat
            $verifikatorData = $data['appVerifikator'] ?? [];

            // Update status_permohonan berdasarkan keputusan verifikator
            if (isset($verifikatorData['keputusan'])) {
                $statusMapping = [
                    'pending' => '1',    // Ditunda
                    'approved' => '2',   // Disetujui  
                    'rejected' => '3',   // Ditolak
                    'revision' => '1',   // Perlu Revisi = Ditunda
                ];

                if (isset($statusMapping[$verifikatorData['keputusan']])) {
                    Log::info("Mapping keputusan '{$verifikatorData['keputusan']}' to status_permohonan '{$statusMapping[$verifikatorData['keputusan']]}'");

                    // Update HANYA status_permohonan dengan query spesifik
                    DB::table('data_pemohon')
                        ->where('id', $record->id)
                        ->update([
                            'status_permohonan' => $statusMapping[$verifikatorData['keputusan']],
                            'updated_at' => now()
                        ]);
                }
            }

            // TIDAK update apapun di tabel data_pemohon kecuali yang sudah dilakukan di atas
            // Hanya update appVerifikator
            if (!empty($verifikatorData)) {
                $verifikatorData['created_at'] = now();
                $verifikatorData['created_by'] = Auth::id();

                $verifikator = $record->latestAppVerifikator ?? $record->appVerifikator()->latest()->first();
                if ($verifikator) {
                    $verifikator->update($verifikatorData);
                } else {
                    AppVerifikator::create(array_merge($verifikatorData, [
                        'pemohon_id' => $record->id,
                    ]));
                }
            }

            // Refresh model instance
            $record->refresh();

            DB::commit();
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in handleRecordUpdate: ' . $e->getMessage());
            Log::error('Data: ' . json_encode($data));
            throw $e;
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure appVerifikator data is properly loaded
        $verifikator = $this->record->latestAppVerifikator ?? $this->record->appVerifikator()->latest()->first();
        if (!isset($data['appVerifikator']) && $verifikator) {
            $data['appVerifikator'] = $verifikator->toArray();
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Debug: Log all incoming data
        Log::info('EditAppVerifikator mutateFormDataBeforeSave - Raw data: ', $data);

        // Remove any field that could cause issues
        $forbiddenFields = ['status', 'id', 'created_at', 'updated_at'];
        foreach ($forbiddenFields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
                Log::info("EditAppVerifikator - Removed forbidden field: {$field}");
            }
        }

        // Only allow specific fields
        $allowedFields = ['status_permohonan', 'appVerifikator'];
        $cleanData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields) || strpos($key, 'appVerifikator.') === 0) {
                $cleanData[$key] = $value;
            } else {
                Log::info("EditAppVerifikator - Filtered out field: {$key}");
            }
        }

        Log::info('EditAppVerifikator mutateFormDataBeforeSave - Clean data: ', $cleanData);
        return $cleanData;
    }
}
