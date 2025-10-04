<?php

namespace App\Services;

use App\Models\DataPemohon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class BapendaService
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'client_id' => config('bapenda.client_id', '1001'),
            'api_url' => config('bapenda.api_url', 'http://10.15.36.91:7071/dpnol_data_assets'),
            'username' => config('bapenda.username', 'samawa'),
            'timeout' => config('bapenda.timeout', 30),
            'mock_mode' => config('bapenda.mock_mode', false),
            'secret_key' => config('bapenda.secret_key', ''), // Add secret key if needed
        ];
    }

    /**
     * Update data Bapenda untuk pemohon berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function updateBapendaDataById(int $id): array
    {
        try {
            Log::info("BapendaService: Starting bapenda data update for ID: {$id}");

            // Ambil data pemohon berdasarkan ID
            $dataPemohon = DataPemohon::find($id);

            if (!$dataPemohon) {
                throw new Exception("Data pemohon with ID {$id} not found");
            }

            if (empty($dataPemohon->nik)) {
                throw new Exception("NIK not found for pemohon ID {$id}");
            }

            // Panggil API Bapenda
            $response = $this->callBapendaApi($dataPemohon->nik);

            if (!$response) {
                throw new Exception("API call failed: No response received. Please check network connectivity and API configuration.");
            }

            if (!$response->successful()) {
                $errorMsg = "API call failed with status {$response->status()}";
                $responseBody = $response->body();
                if (!empty($responseBody)) {
                    $errorMsg .= ": " . $responseBody;
                }
                throw new Exception($errorMsg);
            }

            $apiData = $response->json();

            // Proses dan simpan data
            $this->processBapendaResponse($dataPemohon, $apiData);

            // Generate summary hasil update
            $summary = $this->generateUpdateSummary($dataPemohon);

            Log::info("BapendaService: Successfully updated bapenda data for ID: {$id}");

            return [
                'success' => true,
                'message' => 'Bapenda data successfully updated',
                'id' => $id,
                'nik' => $dataPemohon->nik,
                'id_pendaftaran' => $dataPemohon->id_pendaftaran,
                'summary' => $summary
            ];
        } catch (Exception $e) {
            Log::error("BapendaService: Error updating bapenda data", [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update bapenda data: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'id' => $id
            ];
        }
    }

    /**
     * Update data Bapenda untuk pemohon berdasarkan NIK
     *
     * @param string $nik
     * @return array
     */
    public function updateBapendaDataByNik(string $nik): array
    {
        try {
            Log::info("BapendaService: Starting bapenda data update for NIK: {$nik}");

            // Cari data pemohon berdasarkan NIK
            $dataPemohon = DataPemohon::where('nik', $nik)->first();

            if (!$dataPemohon) {
                throw new Exception("Data pemohon with NIK {$nik} not found");
            }

            // Panggil API Bapenda
            $response = $this->callBapendaApi($nik);

            if (!$response) {
                throw new Exception("API call failed: No response received. Please check network connectivity and API configuration.");
            }

            if (!$response->successful()) {
                $errorMsg = "API call failed with status {$response->status()}";
                $responseBody = $response->body();
                if (!empty($responseBody)) {
                    $errorMsg .= ": " . $responseBody;
                }
                throw new Exception($errorMsg);
            }

            $apiData = $response->json();

            // Proses dan simpan data
            $this->processBapendaResponse($dataPemohon, $apiData);

            // Generate summary hasil update
            $summary = $this->generateUpdateSummary($dataPemohon);

            Log::info("BapendaService: Successfully updated bapenda data for NIK: {$nik}");

            return [
                'success' => true,
                'message' => 'Bapenda data successfully updated',
                'nik' => $nik,
                'id' => $dataPemohon->id,
                'id_pendaftaran' => $dataPemohon->id_pendaftaran,
                'summary' => $summary
            ];
        } catch (Exception $e) {
            Log::error("BapendaService: Error updating bapenda data by NIK", [
                'nik' => $nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update bapenda data: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'nik' => $nik
            ];
        }
    }

    /**
     * Panggil API Bapenda
     *
     * @param string $nik
     * @return \Illuminate\Http\Client\Response|null
     */
    private function callBapendaApi(string $nik): ?\Illuminate\Http\Client\Response
    {
        // Check if mock mode is enabled - return mock response directly in processBapendaResponse
        if ($this->config['mock_mode']) {
            Log::info("BapendaService: Mock mode enabled for NIK: {$nik}");
            // Create simple mock response using Http::fake
            Http::fake([
                '*' => Http::response([
                    'assets_pkb' => [
                        'kendaraan' => [
                            [
                                'jenis' => 'Motor',
                                'merk' => 'Honda',
                                'tahun' => '2020',
                                'no_polisi' => 'B1234ABC',
                                'pajak' => 150000
                            ],
                            [
                                'jenis' => 'Mobil',
                                'merk' => 'Toyota',
                                'tahun' => '2019',
                                'no_polisi' => 'B5678DEF',
                                'pajak' => 500000
                            ]
                        ]
                    ],
                    'assets_pbb' => [
                        'data' => [
                            [
                                'alamat' => 'Jl. Mock Street No. 123, Jakarta',
                                'luas_tanah' => '100',
                                'luas_bangunan' => '80',
                                'njop' => 500000000
                            ]
                        ]
                    ]
                ], 200, ['Content-Type' => 'application/json'])
            ]);

            return Http::get('http://mock-bapenda-api/data');
        }

        $maxRetries = 3;
        $retryDelay = 2; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info("BapendaService: Making API call attempt {$attempt}/{$maxRetries}", [
                    'nik' => $nik,
                    'attempt' => $attempt
                ]);

                $date = now()->format('Y-m-d H:i:s');
                $signature = $this->generateSignature($date);

                $headers = [
                    'Content-Type' => 'application/json',
                    'Date' => $date,
                    'Accept' => 'application/json',
                    'version' => '1.0.0',
                    'clientid' => $this->config['client_id'],
                    'Authorization' => "DA01 {$this->config['username']}:{$signature}",
                ];

                // Don't URL encode NIK - match original PHP implementation
                $url = $this->config['api_url'] . '?nik=' . $nik;

                Log::info("BapendaService: API request details", [
                    'url' => $url,
                    'nik' => $nik,
                    'headers' => [
                        'Date' => $date,
                        'clientid' => $this->config['client_id'],
                        'username' => $this->config['username'],
                        'signature_preview' => substr($signature, 0, 10) . '...'
                    ],
                    'timeout' => $this->config['timeout']
                ]);

                // Try using cURL directly first (matching original PHP implementation)
                $response = $this->callBapendaApiWithCurl($url, $headers);

                // If cURL fails, fallback to HTTP client
                if (!$response) {
                    Log::info("BapendaService: cURL failed, trying HTTP client fallback");
                    $response = Http::withHeaders($headers)
                        ->timeout($this->config['timeout'])
                        ->connectTimeout(10)
                        ->retry(2, 1000)
                        ->withOptions([
                            'verify' => false,
                            'http_errors' => false
                        ])
                        ->get($url);
                }

                Log::info("BapendaService: API response received", [
                    'nik' => $nik,
                    'status_code' => $response->status(),
                    'response_size' => strlen($response->body()),
                    'headers' => $response->headers()
                ]);

                if ($response->successful()) {
                    Log::info("BapendaService: API call successful", [
                        'nik' => $nik,
                        'status' => $response->status(),
                        'attempt' => $attempt
                    ]);
                    return $response;
                } else {
                    Log::warning("BapendaService: API call failed with HTTP error", [
                        'nik' => $nik,
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'attempt' => $attempt
                    ]);

                    // If it's the last attempt, return the response anyway for further handling
                    if ($attempt === $maxRetries) {
                        return $response;
                    }
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error("BapendaService: Connection exception on attempt {$attempt}", [
                    'nik' => $nik,
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'url' => $url ?? 'unknown'
                ]);

                if ($attempt === $maxRetries) {
                    return null;
                }
            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error("BapendaService: Request exception on attempt {$attempt}", [
                    'nik' => $nik,
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'response' => $e->response ? $e->response->body() : null
                ]);

                if ($attempt === $maxRetries) {
                    return null;
                }
            } catch (Exception $e) {
                Log::error("BapendaService: General exception on attempt {$attempt}", [
                    'nik' => $nik,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'attempt' => $attempt
                ]);

                if ($attempt === $maxRetries) {
                    return null;
                }
            }

            // Sleep between retries (except on last attempt)
            if ($attempt < $maxRetries) {
                Log::info("BapendaService: Waiting {$retryDelay} seconds before retry", [
                    'nik' => $nik,
                    'attempt' => $attempt,
                    'next_attempt' => $attempt + 1
                ]);
                sleep($retryDelay);
            }
        }

        Log::error("BapendaService: All retry attempts failed", [
            'nik' => $nik,
            'total_attempts' => $maxRetries
        ]);

        return null;
    }

    /**
     * Generate signature untuk autentikasi API
     * Disesuaikan dengan algoritma dari get_header.php
     *
     * @param string $date
     * @return string
     */
    private function generateSignature(string $date): string
    {
        // Pastikan format yang sesuai dengan server - match original PHP implementation
        $stringToSign = $date . $this->config['client_id'] . $this->config['username'];

        // Gunakan secret key jika tersedia, jika tidak gunakan algoritma default
        if (!empty($this->config['secret_key'])) {
            $signature = hash_hmac('sha256', $stringToSign, $this->config['secret_key']);
        } else {
            $signature = hash('sha256', $stringToSign);
        }

        Log::debug("BapendaService: Signature generation", [
            'date' => $date,
            'client_id' => $this->config['client_id'],
            'username' => $this->config['username'],
            'string_to_sign' => $stringToSign,
            'signature_method' => !empty($this->config['secret_key']) ? 'HMAC-SHA256' : 'SHA256',
            'signature' => $signature
        ]);

        return $signature;
    }

    /**
     * Call Bapenda API using cURL (matching original PHP implementation)
     *
     * @param string $url
     * @param array $headers
     * @return \Illuminate\Http\Client\Response|null
     */
    private function callBapendaApiWithCurl(string $url, array $headers): ?\Illuminate\Http\Client\Response
    {
        try {
            // Convert headers to cURL format (key: value)
            $curlHeaders = [];
            foreach ($headers as $key => $value) {
                $curlHeaders[] = $key . ': ' . $value;
            }

            // Initialize cURL - exact match to original PHP implementation
            $ch = curl_init();

            // Set cURL options - matching original implementation
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout']);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

            Log::debug("BapendaService: cURL request", [
                'url' => $url,
                'headers' => $curlHeaders,
                'timeout' => $this->config['timeout']
            ]);

            // Execute cURL request
            $serverOutput = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                Log::error("BapendaService: cURL error: " . $error);
                return null;
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            Log::debug("BapendaService: cURL response", [
                'http_code' => $httpCode,
                'content_type' => $contentType,
                'response_size' => strlen($serverOutput)
            ]);

            // Create a Laravel HTTP response object from cURL result
            // Use Http::fake to create proper response object
            $responseData = json_decode($serverOutput, true) ?: [];

            Http::fake([
                'curl-response' => Http::response($responseData, $httpCode, ['Content-Type' => $contentType])
            ]);

            return Http::get('curl-response');
        } catch (Exception $e) {
            Log::error("BapendaService: cURL exception", [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            return null;
        }
    }

    /**
     * Test koneksi ke API Bapenda
     *
     * @return array
     */
    public function testApiConnection(): array
    {
        try {
            Log::info("BapendaService: Testing API connection");

            $testNik = '1234567890123456'; // NIK dummy untuk test
            $date = now()->format('Y-m-d H:i:s');
            $signature = $this->generateSignature($date);

            $headers = [
                'Content-Type' => 'application/json',
                'Date' => $date,
                'Accept' => 'application/json',
                'version' => '1.0.0',
                'clientid' => $this->config['client_id'],
                'Authorization' => "DA01 {$this->config['username']}:{$signature}",
            ];

            $url = $this->config['api_url'] . '?nik=' . urlencode($testNik);

            Log::info("BapendaService: Test connection details", [
                'url' => $url,
                'headers' => $headers,
                'config' => $this->config
            ]);

            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->connectTimeout(10)
                ->withOptions([
                    'verify' => false,
                    'http_errors' => false
                ])
                ->get($url);

            $result = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response_size' => strlen($response->body()),
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 500),
                'config_used' => $this->config
            ];

            Log::info("BapendaService: Test connection result", $result);

            return $result;
        } catch (Exception $e) {
            $errorResult = [
                'success' => false,
                'error' => $e->getMessage(),
                'config_used' => $this->config,
                'trace' => $e->getTraceAsString()
            ];

            Log::error("BapendaService: Test connection failed", $errorResult);

            return $errorResult;
        }
    }

    /**
     * Proses response API dan update database
     *
     * @param DataPemohon $dataPemohon
     * @param array $data
     * @return void
     */
    private function processBapendaResponse(DataPemohon $dataPemohon, array $data): void
    {
        DB::beginTransaction();

        try {
            $updateData = [];

            // Proses data PKB (Kendaraan) - simpan ke field bapenda
            if (isset($data['assets_pkb']['kendaraan'])) {
                $kendaraanData = json_encode($data['assets_pkb']['kendaraan']);
                $updateData['bapenda'] = $kendaraanData;

                // Hitung jumlah kendaraan berdasarkan jenis
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

                Log::info("BapendaService: PKB data processed", [
                    'id' => $dataPemohon->id,
                    'nik' => $dataPemohon->nik,
                    'vehicle_count' => count($data['assets_pkb']['kendaraan']),
                    'roda2' => $roda2Count,
                    'roda4' => $roda4Count
                ]);
            }

            // Proses data PBB (Hunian) - simpan ke field aset_hunian
            if (isset($data['assets_pbb']['data'])) {
                $hunianData = json_encode($data['assets_pbb']['data']);
                $updateData['aset_hunian'] = $hunianData;

                Log::info("BapendaService: PBB data processed", [
                    'id' => $dataPemohon->id,
                    'nik' => $dataPemohon->nik,
                    'pbb_records' => count($data['assets_pbb']['data'])
                ]);
            }

            // Jika ada data pasangan, proses juga (NIK2)
            if (!empty($dataPemohon->nik2)) {
                $this->processPasanganBapendaData($dataPemohon, $updateData);
            }

            // Update record jika ada data
            if (!empty($updateData)) {
                // Tambahkan timestamp update
                $updateData['bapenda_updated_at'] = now();

                $dataPemohon->update($updateData);

                Log::info("BapendaService: Database updated successfully", [
                    'id' => $dataPemohon->id,
                    'nik' => $dataPemohon->nik,
                    'updated_fields' => array_keys($updateData)
                ]);
            } else {
                Log::warning("BapendaService: No valid data found in API response", [
                    'id' => $dataPemohon->id,
                    'nik' => $dataPemohon->nik,
                    'response_keys' => array_keys($data)
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error("BapendaService: Failed to process bapenda response", [
                'id' => $dataPemohon->id,
                'nik' => $dataPemohon->nik,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Proses data Bapenda untuk pasangan (NIK2)
     *
     * @param DataPemohon $dataPemohon
     * @param array &$updateData
     * @return void
     */
    private function processPasanganBapendaData(DataPemohon $dataPemohon, array &$updateData): void
    {
        try {
            Log::info("BapendaService: Processing pasangan bapenda data for NIK2: {$dataPemohon->nik2}");

            // Panggil API untuk NIK pasangan
            $responsePasangan = $this->callBapendaApi($dataPemohon->nik2);

            if ($responsePasangan && $responsePasangan->successful()) {
                $dataPasangan = $responsePasangan->json();

                // Proses data PKB pasangan
                if (isset($dataPasangan['assets_pkb']['kendaraan'])) {
                    $kendaraanPasanganData = json_encode($dataPasangan['assets_pkb']['kendaraan']);
                    $updateData['bapenda_pasangan'] = $kendaraanPasanganData;

                    Log::info("BapendaService: Pasangan PKB data processed", [
                        'id' => $dataPemohon->id,
                        'nik2' => $dataPemohon->nik2,
                        'vehicle_count' => count($dataPasangan['assets_pkb']['kendaraan'])
                    ]);
                }

                // Proses data PBB pasangan
                if (isset($dataPasangan['assets_pbb']['data'])) {
                    $pbbPasanganData = json_encode($dataPasangan['assets_pbb']['data']);
                    $updateData['bapenda_pasangan_pbb'] = $pbbPasanganData;

                    Log::info("BapendaService: Pasangan PBB data processed", [
                        'id' => $dataPemohon->id,
                        'nik2' => $dataPemohon->nik2,
                        'pbb_records' => count($dataPasangan['assets_pbb']['data'])
                    ]);
                }
            } else {
                Log::warning("BapendaService: Failed to get pasangan bapenda data", [
                    'id' => $dataPemohon->id,
                    'nik2' => $dataPemohon->nik2,
                    'status' => $responsePasangan ? $responsePasangan->status() : 'No response'
                ]);
            }
        } catch (Exception $e) {
            Log::error("BapendaService: Error processing pasangan bapenda data", [
                'id' => $dataPemohon->id,
                'nik2' => $dataPemohon->nik2,
                'error' => $e->getMessage()
            ]);
            // Jangan throw exception disini, karena ini optional
        }
    }

    /**
     * Generate summary hasil update Bapenda
     *
     * @param DataPemohon $dataPemohon
     * @return array
     */
    private function generateUpdateSummary(DataPemohon $dataPemohon): array
    {
        // Refresh data dari database untuk mendapatkan data terbaru
        $dataPemohon->refresh();

        $summary = [
            'updated_at' => $dataPemohon->bapenda_updated_at?->format('Y-m-d H:i:s'),
            'pemohon' => [
                'has_bapenda_data' => !empty($dataPemohon->bapenda),
                'has_aset_hunian_data' => !empty($dataPemohon->aset_hunian),
                'vehicle_count' => [
                    'roda2' => $dataPemohon->count_of_vehicle1 ?? 0,
                    'roda4' => $dataPemohon->count_of_vehicle2 ?? 0,
                    'total' => ($dataPemohon->count_of_vehicle1 ?? 0) + ($dataPemohon->count_of_vehicle2 ?? 0)
                ]
            ],
            'pasangan' => [
                'has_bapenda_data' => !empty($dataPemohon->bapenda_pasangan),
                'has_pbb_data' => !empty($dataPemohon->bapenda_pasangan_pbb),
                'nik2' => $dataPemohon->nik2
            ]
        ];

        // Hitung detail kendaraan jika ada data
        if (!empty($dataPemohon->bapenda)) {
            try {
                $bapendaData = json_decode($dataPemohon->bapenda, true);
                $summary['pemohon']['vehicles'] = count($bapendaData);

                $vehicleTypes = [];
                foreach ($bapendaData as $vehicle) {
                    $jenis = $vehicle['jenis'] ?? 'Unknown';
                    $vehicleTypes[$jenis] = ($vehicleTypes[$jenis] ?? 0) + 1;
                }
                $summary['pemohon']['vehicle_types'] = $vehicleTypes;
            } catch (\Exception $e) {
                Log::warning("Failed to parse bapenda data for summary", [
                    'id' => $dataPemohon->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Hitung detail aset hunian jika ada data
        if (!empty($dataPemohon->aset_hunian)) {
            try {
                $asetHunianData = json_decode($dataPemohon->aset_hunian, true);
                $summary['pemohon']['properties'] = count($asetHunianData);
            } catch (\Exception $e) {
                Log::warning("Failed to parse aset_hunian data for summary", [
                    'id' => $dataPemohon->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Hitung detail kendaraan pasangan jika ada data
        if (!empty($dataPemohon->bapenda_pasangan)) {
            try {
                $bapendaPasanganData = json_decode($dataPemohon->bapenda_pasangan, true);
                $summary['pasangan']['vehicles'] = count($bapendaPasanganData);
            } catch (\Exception $e) {
                Log::warning("Failed to parse bapenda_pasangan data for summary", [
                    'id' => $dataPemohon->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Hitung detail PBB pasangan jika ada data
        if (!empty($dataPemohon->bapenda_pasangan_pbb)) {
            try {
                $pbbPasanganData = json_decode($dataPemohon->bapenda_pasangan_pbb, true);
                $summary['pasangan']['properties'] = count($pbbPasanganData);
            } catch (\Exception $e) {
                Log::warning("Failed to parse bapenda_pasangan_pbb data for summary", [
                    'id' => $dataPemohon->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $summary;
    }
}
