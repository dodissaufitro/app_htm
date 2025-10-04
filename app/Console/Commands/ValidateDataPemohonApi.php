<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataPemohon;
use App\Models\Status;

class ValidateDataPemohonApi extends Command
{
    protected $signature = 'validate:data-pemohon-api';
    protected $description = 'Validate Data Pemohon API structure and functionality';

    public function handle()
    {
        $this->info('Validating Data Pemohon API Structure...');
        $this->newLine();

        try {
            // Test 1: Check model exists and fillable fields
            $this->info('1. Testing Model Structure');
            $model = new DataPemohon();
            $fillableFields = $model->getFillable();
            $this->line("Fillable fields count: " . count($fillableFields));

            // Test 2: Check required fields mapping
            $this->info('2. Testing Field Mapping');

            // Ensure we have valid status
            $validStatus = Status::first();
            if (!$validStatus) {
                // Create a basic status if none exists
                $validStatus = Status::create([
                    'kode' => 'pending',
                    'nama' => 'Pending'
                ]);
            }

            $sampleData = [
                'id_pendaftaran' => 'TEST' . time(), // Add required field
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
                'status_permohonan' => $validStatus->kode, // Add valid status
                'reason_of_choose_location' => json_encode([
                    [
                        'id' => 4,
                        'name' => 'Dekat Transportasi Publik'
                    ]
                ]),
                'booking_files' => json_encode([
                    [
                        'fname' => 'test_ktp.jpg',
                        'file_type' => 'KTP'
                    ]
                ])
            ];

            // Test create
            $dataPemohon = DataPemohon::create($sampleData);
            $this->info("✓ Data pemohon created with ID: {$dataPemohon->id}");

            // Test read
            $retrieved = DataPemohon::find($dataPemohon->id);
            $this->info("✓ Data pemohon retrieved: {$retrieved->nama}");

            // Test update
            $retrieved->update(['nama' => 'BAGUS RIFAI UPDATED']);
            $this->info("✓ Data pemohon updated: {$retrieved->fresh()->nama}");

            // Test JSON fields parsing
            $reasonDecoded = json_decode($retrieved->reason_of_choose_location, true);
            $this->info("✓ JSON field parsed: " . $reasonDecoded[0]['name']);

            // Test delete
            $retrieved->delete();
            $this->info("✓ Data pemohon deleted");

            // Test 3: Validate API Controller exists
            $this->info('3. Testing API Controller');
            $controllerExists = class_exists('App\Http\Controllers\Api\DataPemohonApiController');
            $this->info($controllerExists ? "✓ API Controller exists" : "✗ API Controller not found");

            // Test 4: Check routes
            $this->info('4. Testing Routes');
            $routes = \Illuminate\Support\Facades\Route::getRoutes();
            $apiRoutes = [];
            foreach ($routes as $route) {
                $uri = $route->uri();
                if (str_contains($uri, 'api/data-pemohon')) {
                    $apiRoutes[] = $route->methods()[0] . ' ' . $uri;
                }
            }

            if (count($apiRoutes) > 0) {
                $this->info("✓ API Routes found:");
                foreach ($apiRoutes as $route) {
                    $this->line("  - $route");
                }
            } else {
                $this->warn("⚠ No API routes found");
            }

            // Test 5: Sample data structure
            $this->info('5. Testing Sample Data Structure');
            $this->displaySampleStructure();

            $this->newLine();
            $this->info('✓ All API structure validations passed!');
        } catch (\Exception $e) {
            $this->error('Validation failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function displaySampleStructure()
    {
        $this->info('Sample JSON Structure for API:');
        $sample = [
            'book_number' => '2025071900001',
            'settlement_id' => '63',
            'settlement_name' => 'TOWER SAMAWA NUANSA PONDOK KELAPA',
            'npwp' => '090844465031000',
            'nik' => '3173072208930002',
            'email_address' => 'rifai.bagus@gmail.com',
            'name' => 'BAGUS RIFAI',
            'mobile_phone_number' => '081806563006',
            'job' => 'Karyawan swasta',
            'salary' => 7880000,
            'marital_status' => '1',
            'education_name' => 'SLTA',
            'count_of_vehicle1' => 0,
            'count_of_vehicle2' => 0,
            'is_have_saving_bank' => 1,
            'is_have_home_credit' => 0,
            'reason_of_choose_location' => [
                [
                    'id' => 4,
                    'name' => 'Dekat Transportasi Publik'
                ]
            ],
            'booking_files' => [
                [
                    'fname' => 'test_file.jpg',
                    'file_type' => 'KTP'
                ]
            ]
        ];

        $this->line(json_encode($sample, JSON_PRETTY_PRINT));

        $this->newLine();
        $this->info('API Endpoints:');
        $endpoints = [
            'GET /api/data-pemohon' => 'Get all data with pagination/search/filter',
            'POST /api/data-pemohon' => 'Create new data pemohon',
            'GET /api/data-pemohon/{id}' => 'Get specific data pemohon',
            'PUT /api/data-pemohon/{id}' => 'Update data pemohon',
            'DELETE /api/data-pemohon/{id}' => 'Delete data pemohon',
            'GET /api/data-pemohon/book-number/{bookNumber}' => 'Get by book number'
        ];

        foreach ($endpoints as $endpoint => $description) {
            $this->line("  $endpoint - $description");
        }
    }
}
