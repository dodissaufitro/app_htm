<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DataPemohon;
use App\Services\BapendaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class BapendaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_bapenda_data_by_id()
    {
        // Create test data
        $dataPemohon = DataPemohon::factory()->create([
            'nik' => '1234567890123456',
            'nama' => 'Test User'
        ]);

        // Mock API response
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
                        ]
                    ]
                ],
                'assets_pbb' => [
                    'data' => [
                        [
                            'alamat' => 'Jl. Test No. 123',
                            'luas_tanah' => '100',
                            'luas_bangunan' => '80',
                            'njop' => 500000000
                        ]
                    ]
                ]
            ], 200)
        ]);

        $service = new BapendaService();
        $result = $service->updateBapendaDataById($dataPemohon->id);

        $this->assertTrue($result['success']);
        $this->assertEquals($dataPemohon->id, $result['id']);

        // Verify data was saved
        $dataPemohon->refresh();
        $this->assertNotNull($dataPemohon->bapenda);
        $this->assertNotNull($dataPemohon->aset_hunian);
    }

    public function test_returns_error_when_pemohon_not_found()
    {
        $service = new BapendaService();
        $result = $service->updateBapendaDataById(999);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', $result['message']);
    }

    public function test_returns_error_when_nik_is_empty()
    {
        $dataPemohon = DataPemohon::factory()->create([
            'nik' => null
        ]);

        $service = new BapendaService();
        $result = $service->updateBapendaDataById($dataPemohon->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('NIK not found', $result['message']);
    }
}
