<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApiDataPemohon extends Command
{
    protected $signature = 'test:api-data-pemohon';
    protected $description = 'Test Data Pemohon API endpoints';

    public function handle()
    {
        $this->info('🧪 Testing Data Pemohon API Endpoints...');

        $baseUrl = config('app.url') . '/api';

        // Test data
        $testData = [
            'book_number' => '2025100200001',
            'settlement_name' => 'TOWER SAMAWA NUANSA PONDOK KELAPA',
            'npwp' => '090844465031000',
            'nik' => '3173072208930002',
            'email_address' => 'test.api@gmail.com',
            'name' => 'TEST API USER',
            'mobile_phone_number' => '081806563006',
            'job' => 'Karyawan swasta',
            'salary' => '7880000',
            'marital_status' => '1',
            'is_couple_dki' => false,
            'is_have_booking_kpr_dpnol' => true,
            'is_valid_npwp' => false,
            'education_name' => 'SLTA',
            'residence_status_name' => 'Orang Tua',
            'correspondence_address' => 'Alamat KTP',
            'is_domicile_same_with_ektp' => '1',
            'count_of_vehicle1' => '0',
            'count_of_vehicle2' => '0',
            'is_have_saving_bank' => true,
            'is_have_home_credit' => false,
            'atpid' => '2',
            'mounthly_expense1' => '500000',
            'mounthly_expense2' => '4000000',
            'aset_hunian' => [],
            'reason_of_choose_location' => [
                [
                    'id' => 4,
                    'name' => 'Dekat Transportasi Publik'
                ]
            ],
            'government_assistance_aid' => [],
            'booking_files' => [
                [
                    'fname' => 'test_ktp.jpg',
                    'base64' => null,
                    'file_type' => 'KTP'
                ],
                [
                    'fname' => 'test_kk.jpg',
                    'base64' => null,
                    'file_type' => 'KK'
                ]
            ]
        ];

        try {
            // 1. Test POST - Create Data Pemohon
            $this->info('1. Testing POST /api/data-pemohon');
            $response = Http::post("{$baseUrl}/data-pemohon", $testData);

            if ($response->successful()) {
                $responseData = $response->json();
                $this->info('✅ POST Success: ' . $response->status());
                $this->line('   Created ID: ' . $responseData['data']['id']);
                $this->line('   ID Pendaftaran: ' . $responseData['data']['id_pendaftaran']);

                $createdId = $responseData['data']['id'];
                $bookNumber = $responseData['data']['id_pendaftaran'];

                // 2. Test GET by ID
                $this->info('2. Testing GET /api/data-pemohon/{id}');
                $getResponse = Http::get("{$baseUrl}/data-pemohon/{$createdId}");

                if ($getResponse->successful()) {
                    $this->info('✅ GET by ID Success: ' . $getResponse->status());
                    $getData = $getResponse->json();
                    $this->line('   Name: ' . $getData['data']['name']);
                    $this->line('   NIK: ' . $getData['data']['nik']);
                } else {
                    $this->error('❌ GET by ID Failed: ' . $getResponse->status());
                    $this->line('   Error: ' . $getResponse->body());
                }

                // 3. Test GET by Book Number
                $this->info('3. Testing GET /api/data-pemohon/{book_number}');
                $getByBookResponse = Http::get("{$baseUrl}/data-pemohon/{$bookNumber}");

                if ($getByBookResponse->successful()) {
                    $this->info('✅ GET by Book Number Success: ' . $getByBookResponse->status());
                } else {
                    $this->error('❌ GET by Book Number Failed: ' . $getByBookResponse->status());
                }

                // 4. Test GET List with pagination
                $this->info('4. Testing GET /api/data-pemohon (list)');
                $listResponse = Http::get("{$baseUrl}/data-pemohon?per_page=5");

                if ($listResponse->successful()) {
                    $listData = $listResponse->json();
                    $this->info('✅ GET List Success: ' . $listResponse->status());
                    $this->line('   Total records: ' . $listData['meta']['total']);
                    $this->line('   Current page: ' . $listData['meta']['current_page']);
                } else {
                    $this->error('❌ GET List Failed: ' . $listResponse->status());
                }

                // 5. Test PUT - Update
                $this->info('5. Testing PUT /api/data-pemohon/{id}');
                $updateData = [
                    'name' => 'TEST API USER UPDATED',
                    'salary' => '8500000',
                    'mobile_phone_number' => '081806563007'
                ];
                $updateResponse = Http::put("{$baseUrl}/data-pemohon/{$createdId}", $updateData);

                if ($updateResponse->successful()) {
                    $this->info('✅ PUT Success: ' . $updateResponse->status());
                    $updateResponseData = $updateResponse->json();
                    $this->line('   Updated name: ' . $updateResponseData['data']['nama']);
                } else {
                    $this->error('❌ PUT Failed: ' . $updateResponse->status());
                    $this->line('   Error: ' . $updateResponse->body());
                }

                // 6. Test Search
                $this->info('6. Testing GET /api/data-pemohon with search');
                $searchResponse = Http::get("{$baseUrl}/data-pemohon?search=TEST&per_page=10");

                if ($searchResponse->successful()) {
                    $searchData = $searchResponse->json();
                    $this->info('✅ Search Success: ' . $searchResponse->status());
                    $this->line('   Found records: ' . count($searchData['data']));
                } else {
                    $this->error('❌ Search Failed: ' . $searchResponse->status());
                }

                // 7. Test DELETE (optional - uncomment if you want to test delete)
                /*
                $this->info('7. Testing DELETE /api/data-pemohon/{id}');
                $deleteResponse = Http::delete("{$baseUrl}/data-pemohon/{$createdId}");
                
                if ($deleteResponse->successful()) {
                    $this->info('✅ DELETE Success: ' . $deleteResponse->status());
                } else {
                    $this->error('❌ DELETE Failed: ' . $deleteResponse->status());
                }
                */
            } else {
                $this->error('❌ POST Failed: ' . $response->status());
                $this->line('   Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('💥 Exception occurred: ' . $e->getMessage());
        }

        // Test validation error
        $this->info('8. Testing validation error');
        $invalidData = ['invalid' => 'data']; // Missing required fields
        $validationResponse = Http::post("{$baseUrl}/data-pemohon", $invalidData);

        if ($validationResponse->status() === 422) {
            $this->info('✅ Validation Error Test Success: ' . $validationResponse->status());
            $validationData = $validationResponse->json();
            $this->line('   Validation errors detected: ' . count($validationData['errors']));
        } else {
            $this->error('❌ Validation Error Test Failed: Expected 422, got ' . $validationResponse->status());
        }

        $this->info('🎉 API Testing Complete!');
    }
}
