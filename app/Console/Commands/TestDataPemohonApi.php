<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\DataPemohon;
use App\Models\Status;

class TestDataPemohonApi extends Command
{
    protected $signature = 'test:data-pemohon-api';
    protected $description = 'Test Data Pemohon API endpoints';

    public function handle()
    {
        $this->info('Testing Data Pemohon API Endpoints...');
        $this->newLine();

        $baseUrl = config('app.url') . '/api/data-pemohon';

        try {
            // Test 1: Get all data pemohon
            $this->info('1. Testing GET /api/data-pemohon');
            $response = Http::get($baseUrl);
            $this->displayApiResponse('GET All', $response);

            // Test 2: Create new data pemohon
            $this->info('2. Testing POST /api/data-pemohon');
            $sampleData = [
                'username' => 'test_user_' . time(),
                'nik' => '3173072208930002',
                'nama' => 'BAGUS RIFAI TEST',
                'email_address' => 'test@example.com',
                'mobile_phone_number' => '081806563006',
                'job' => 'Karyawan swasta',
                'salary' => 7880000,
                'marital_status' => '1',
                'is_couple_dki' => 0,
                'is_have_booking_kpr_dpnol' => 1,
                'education_name' => 'SLTA',
                'residence_status_name' => 'Orang Tua',
                'correspondence_address' => 'Alamat KTP',
                'is_domicile_same_with_ektp' => '1',
                'count_of_vehicle1' => 0,
                'count_of_vehicle2' => 0,
                'is_have_saving_bank' => 1,
                'is_have_home_credit' => 0,
                'atp_name' => 'Rp 1.500.000 - Rp 2.000.000',
                'mounthly_expense1' => 500000,
                'mounthly_expense2' => 4000000,
                'settlement_name' => 'TOWER SAMAWA NUANSA PONDOK KELAPA',
                'reason_of_choose_location' => [
                    [
                        'id' => 4,
                        'name' => 'Dekat Transportasi Publik'
                    ]
                ],
                'booking_files' => [
                    [
                        'fname' => 'test_ktp.jpg',
                        'file_type' => 'KTP'
                    ],
                    [
                        'fname' => 'test_kk.jpg',
                        'file_type' => 'KK'
                    ]
                ]
            ];

            $response = Http::post($baseUrl, $sampleData);
            $this->displayApiResponse('POST Create', $response);

            $createdId = null;
            if ($response->successful()) {
                $createdData = $response->json();
                $createdId = $createdData['data']['id'] ?? null;
            }

            // Test 3: Get single data pemohon
            if ($createdId) {
                $this->info('3. Testing GET /api/data-pemohon/{id}');
                $response = Http::get($baseUrl . '/' . $createdId);
                $this->displayApiResponse('GET Single', $response);

                // Test 4: Update data pemohon
                $this->info('4. Testing PUT /api/data-pemohon/{id}');
                $updateData = [
                    'nama' => 'BAGUS RIFAI UPDATED',
                    'salary' => 8000000,
                    'job' => 'Senior Karyawan Swasta'
                ];
                $response = Http::put($baseUrl . '/' . $createdId, $updateData);
                $this->displayApiResponse('PUT Update', $response);
            }

            // Test 5: Search functionality
            $this->info('5. Testing GET /api/data-pemohon with search');
            $response = Http::get($baseUrl . '?search=BAGUS');
            $this->displayApiResponse('GET Search', $response);

            // Test 6: Get by book number (if exists)
            $firstPemohon = DataPemohon::first();
            if ($firstPemohon) {
                $this->info('6. Testing GET /api/data-pemohon/book-number/{bookNumber}');
                $response = Http::get($baseUrl . '/book-number/' . $firstPemohon->id_pendaftaran);
                $this->displayApiResponse('GET By Book Number', $response);
            }

            // Test 7: Status filter
            $this->info('7. Testing GET /api/data-pemohon with status filter');
            $response = Http::get($baseUrl . '?status=pending');
            $this->displayApiResponse('GET Filter Status', $response);

            // Test 8: Delete (if created)
            if ($createdId) {
                $this->info('8. Testing DELETE /api/data-pemohon/{id}');
                $response = Http::delete($baseUrl . '/' . $createdId);
                $this->displayApiResponse('DELETE', $response);
            }

            $this->newLine();
            $this->info('✓ All API tests completed!');

            // Display available endpoints
            $this->displayEndpoints();
        } catch (\Exception $e) {
            $this->error('API test failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function displayApiResponse($testName, $response)
    {
        $statusCode = $response->status();
        $statusColor = $statusCode >= 200 && $statusCode < 300 ? 'info' : 'error';

        $this->$statusColor("Status: {$statusCode}");

        if ($response->successful()) {
            $data = $response->json();
            $this->line("Message: " . ($data['message'] ?? 'Success'));

            if (isset($data['data'])) {
                if (is_array($data['data']) && isset($data['data']['data'])) {
                    // Paginated response
                    $this->line("Total records: " . $data['data']['total']);
                    $this->line("Per page: " . $data['data']['per_page']);
                } else {
                    $this->line("Data returned: ✓");
                }
            }
        } else {
            $this->error("Error: " . $response->body());
        }

        $this->newLine();
    }

    private function displayEndpoints()
    {
        $this->info('Available API Endpoints:');
        $this->table(
            ['Method', 'Endpoint', 'Description'],
            [
                ['GET', '/api/data-pemohon', 'Get all data pemohon (with pagination, search, filter)'],
                ['POST', '/api/data-pemohon', 'Create new data pemohon'],
                ['GET', '/api/data-pemohon/{id}', 'Get specific data pemohon by ID'],
                ['PUT/PATCH', '/api/data-pemohon/{id}', 'Update data pemohon'],
                ['DELETE', '/api/data-pemohon/{id}', 'Delete data pemohon'],
                ['GET', '/api/data-pemohon/book-number/{bookNumber}', 'Get data pemohon by book number'],
            ]
        );

        $this->newLine();
        $this->info('Query Parameters:');
        $this->table(
            ['Parameter', 'Description', 'Example'],
            [
                ['search', 'Search by name, NIK, or registration ID', '?search=BAGUS'],
                ['status', 'Filter by status', '?status=pending'],
                ['per_page', 'Number of records per page', '?per_page=10'],
                ['page', 'Page number for pagination', '?page=2'],
            ]
        );
    }
}
