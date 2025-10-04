<?php

namespace App\Filament\Resources\AppVerifikatorResource\Pages;

use App\Filament\Resources\AppVerifikatorResource;
use App\Models\AppVerifikator;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateAppVerifikator extends CreateRecord
{
    protected static string $resource = AppVerifikatorResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            DB::beginTransaction();

            // Debug: Log data yang masuk
            Log::info('CreateAppVerifikator - Data received: ', $data);

            // Pisahkan data dengan filtering ketat
            $verifikatorData = $data['appVerifikator'] ?? [];

            // Data pemohon hanya field yang benar-benar diperlukan
            $allowedPemohonFields = ['nama', 'nik', 'no_hp', 'gaji', 'id_bank', 'status_permohonan'];
            $pemohonData = [];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedPemohonFields)) {
                    $pemohonData[$key] = $value;
                }
            }

            // Pastikan tidak ada field 'status' yang lolos
            if (isset($pemohonData['status'])) {
                unset($pemohonData['status']);
                Log::info('CreateAppVerifikator - Removed status field from pemohon data');
            }

            Log::info('CreateAppVerifikator - Final pemohon data: ', $pemohonData);

            // Create data pemohon dengan query eksplisit untuk menghindari mass assignment
            $record = new (static::getModel());
            foreach ($pemohonData as $key => $value) {
                $record->$key = $value;
            }
            $record->save();

            // Update status_permohonan berdasarkan keputusan verifikator
            if (!empty($verifikatorData) && isset($verifikatorData['keputusan'])) {
                $statusMapping = [
                    'pending' => '1',    // Ditunda
                    'approved' => '2',   // Disetujui
                    'rejected' => '3',   // Ditolak
                    'revision' => '1',   // Perlu Revisi = Ditunda
                ];

                if (isset($statusMapping[$verifikatorData['keputusan']])) {
                    DB::table('data_pemohon')
                        ->where('id', $record->id)
                        ->update([
                            'status_permohonan' => $statusMapping[$verifikatorData['keputusan']],
                            'updated_at' => now()
                        ]);
                }
            }

            // Create appVerifikator
            if (!empty($verifikatorData)) {
                $verifikatorData['pemohon_id'] = $record->id;
                $verifikatorData['created_at'] = now();
                $verifikatorData['created_by'] = Auth::id();

                AppVerifikator::create($verifikatorData);
            }

            // Refresh record
            $record->refresh();

            DB::commit();
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in CreateAppVerifikator handleRecordCreation: ' . $e->getMessage());
            Log::error('Data: ' . json_encode($data));
            throw $e;
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Debug: Log all incoming data
        Log::info('CreateAppVerifikator mutateFormDataBeforeSave - Raw data: ', $data);

        // Remove any field that could cause issues
        $forbiddenFields = ['status', 'id', 'created_at', 'updated_at'];
        foreach ($forbiddenFields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
                Log::info("CreateAppVerifikator - Removed forbidden field: {$field}");
            }
        }

        // Only allow specific fields
        $allowedFields = ['nama', 'nik', 'no_hp', 'gaji', 'id_bank', 'status_permohonan', 'appVerifikator'];
        $cleanData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields) || strpos($key, 'appVerifikator.') === 0) {
                $cleanData[$key] = $value;
            } else {
                Log::info("CreateAppVerifikator - Filtered out field: {$key}");
            }
        }

        Log::info('CreateAppVerifikator mutateFormDataBeforeSave - Clean data: ', $cleanData);
        return $cleanData;
    }
}
