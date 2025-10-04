<?php

namespace App\Observers;

use App\Models\DataPemohon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class BapendaObserver
{
    /**
     * Handle the DataPemohon "updated" event.
     */
    public function updated(DataPemohon $dataPemohon): void
    {
        // Check if this is a status update that requires bapenda data refresh
        if ($this->shouldUpdateBapendaData($dataPemohon)) {
            $this->updateBapendaData($dataPemohon);
        }
    }

    /**
     * Handle the DataPemohon "created" event.
     */
    public function created(DataPemohon $dataPemohon): void
    {
        // Update bapenda data for new records
        if ($dataPemohon->nik) {
            $this->updateBapendaData($dataPemohon);
        }
    }

    /**
     * Determine if bapenda data should be updated
     */
    private function shouldUpdateBapendaData(DataPemohon $dataPemohon): bool
    {
        // Update bapenda data if:
        // 1. NIK has changed
        // 2. Status permohonan changed to specific status (e.g., persetujuan)
        // 3. Bapenda data is empty and NIK exists

        $isDirty = $dataPemohon->isDirty(['nik', 'status_permohonan']);
        $hasNik = !empty($dataPemohon->nik);
        $isPersetujuanStatus = in_array($dataPemohon->status_permohonan, ['1', '2']); // Ditunda or Disetujui
        $bapendaEmpty = empty($dataPemohon->bapenda);

        return $hasNik && ($isDirty || ($isPersetujuanStatus && $bapendaEmpty));
    }

    /**
     * Update bapenda data from external API
     */
    private function updateBapendaData(DataPemohon $dataPemohon): void
    {
        try {
            Log::info("BapendaObserver: Starting bapenda data update for NIK: {$dataPemohon->nik}");

            // Get API configuration
            $apiConfig = $this->getApiConfig();

            if (!$apiConfig) {
                Log::error("BapendaObserver: API configuration not found");
                return;
            }

            // Make API call to get bapenda data
            $response = $this->callBapendaApi($dataPemohon->nik, $apiConfig);

            if ($response && $response->successful()) {
                $data = $response->json();
                $this->processBapendaResponse($dataPemohon, $data);
            } else {
                Log::error("BapendaObserver: API call failed", [
                    'nik' => $dataPemohon->nik,
                    'status' => $response ? $response->status() : 'No response',
                    'body' => $response ? $response->body() : 'No body'
                ]);
            }
        } catch (Exception $e) {
            Log::error("BapendaObserver: Exception occurred while updating bapenda data", [
                'nik' => $dataPemohon->nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get API configuration
     */
    private function getApiConfig(): ?array
    {
        // You can move these to config file or environment variables
        $clientId = config('bapenda.client_id', '1001');
        $apiUrl = config('bapenda.api_url', 'http://10.15.36.91:7071/dpnol_data_assets');
        $username = config('bapenda.username', 'samawa');

        if (!$apiUrl) {
            return null;
        }

        return [
            'client_id' => $clientId,
            'api_url' => $apiUrl,
            'username' => $username,
        ];
    }

    /**
     * Generate signature for API authentication
     */
    private function generateSignature(string $date, array $config): string
    {
        // Implement your signature generation logic here
        // This should match the logic from get_header.php
        $stringToSign = $date . $config['client_id'] . $config['username'];
        return hash('sha256', $stringToSign); // Adjust as needed
    }

    /**
     * Make API call to bapenda service
     */
    private function callBapendaApi(string $nik, array $config): ?\Illuminate\Http\Client\Response
    {
        $date = now()->format('Y-m-d H:i:s'); // Adjust format as needed
        $signature = $this->generateSignature($date, $config);

        $headers = [
            'Content-Type' => 'application/json',
            'Date' => $date,
            'Accept' => 'application/json',
            'version' => '1.0.0',
            'clientid' => $config['client_id'],
            'Authorization' => "DA01 {$config['username']}:{$signature}",
        ];

        $url = $config['api_url'] . '?nik=' . urlencode($nik);

        Log::info("BapendaObserver: Making API call", [
            'url' => $url,
            'headers' => array_keys($headers) // Log header keys only for security
        ]);

        return Http::withHeaders($headers)
            ->timeout(30)
            ->get($url);
    }

    /**
     * Process the API response and update database
     */
    private function processBapendaResponse(DataPemohon $dataPemohon, array $data): void
    {
        DB::beginTransaction();

        try {
            $updateData = [];

            // Process PKB (Kendaraan) data
            if (isset($data['assets_pkb']['kendaraan'])) {
                $kendaraanData = json_encode($data['assets_pkb']['kendaraan']);
                $updateData['bapenda'] = $kendaraanData;

                // Count vehicles for summary
                $vehicles = $data['assets_pkb']['kendaraan'];
                $roda2Count = 0;
                $roda4Count = 0;

                foreach ($vehicles as $vehicle) {
                    if (isset($vehicle['jenis'])) {
                        if (
                            stripos($vehicle['jenis'], 'motor') !== false ||
                            stripos($vehicle['jenis'], 'roda 2') !== false
                        ) {
                            $roda2Count++;
                        } else {
                            $roda4Count++;
                        }
                    }
                }

                $updateData['count_of_vehicle1'] = $roda2Count;
                $updateData['count_of_vehicle2'] = $roda4Count;

                Log::info("BapendaObserver: PKB data processed", [
                    'nik' => $dataPemohon->nik,
                    'vehicle_count' => count($vehicles),
                    'roda2' => $roda2Count,
                    'roda4' => $roda4Count
                ]);
            }

            // Process PBB (Hunian) data
            if (isset($data['assets_pbb']['data'])) {
                $hunianData = json_encode($data['assets_pbb']['data']);
                $updateData['aset_hunian'] = $hunianData;

                Log::info("BapendaObserver: PBB data processed", [
                    'nik' => $dataPemohon->nik,
                    'pbb_records' => count($data['assets_pbb']['data'])
                ]);
            }

            // Update the record if we have data
            if (!empty($updateData)) {
                $updateData['bapenda_updated_at'] = now();

                $dataPemohon->updateQuietly($updateData);

                Log::info("BapendaObserver: Database updated successfully", [
                    'nik' => $dataPemohon->nik,
                    'updated_fields' => array_keys($updateData)
                ]);
            } else {
                Log::warning("BapendaObserver: No valid data found in API response", [
                    'nik' => $dataPemohon->nik,
                    'response_keys' => array_keys($data)
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error("BapendaObserver: Failed to process bapenda response", [
                'nik' => $dataPemohon->nik,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle manual bapenda data refresh
     */
    public static function refreshBapendaData(DataPemohon $dataPemohon): bool
    {
        $observer = new self();

        try {
            $observer->updateBapendaData($dataPemohon);
            return true;
        } catch (Exception $e) {
            Log::error("BapendaObserver: Manual refresh failed", [
                'nik' => $dataPemohon->nik,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
