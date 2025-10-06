<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataHunian;

class CreateSampleDataHunianCommand extends Command
{
    protected $signature = 'create:sample-data-hunian';
    protected $description = 'Create sample DataHunian records for testing';

    public function handle()
    {
        $this->info("🏠 Creating Sample DataHunian Records");
        $this->newLine();

        $sampleData = [
            [
                'nama_pemukiman' => 'Perumahan Green Valley',
                'alamat_pemukiman' => 'Jl. Green Valley No. 123, Bintaro',
                'kode_lokasi' => 'GV001',
                'kode_hunian' => 'GV-A1',
                'tipe_hunian' => 'Rumah Tapak',
                'ukuran' => '120 m²',
                'harga' => 850000000,
                'tahun5' => 900000000,
                'tahun10' => 950000000,
                'tahun15' => 1000000000,
                'tahun20' => 1100000000,
                'deleted' => 0,
                'create_date' => now(),
                'update_date' => now(),
            ],
            [
                'nama_pemukiman' => 'Cluster Harmony Heights',
                'alamat_pemukiman' => 'Jl. Harmony Raya No. 456, Serpong',
                'kode_lokasi' => 'HH002',
                'kode_hunian' => 'HH-B2',
                'tipe_hunian' => 'Rumah Cluster',
                'ukuran' => '90 m²',
                'harga' => 650000000,
                'tahun5' => 700000000,
                'tahun10' => 750000000,
                'tahun15' => 800000000,
                'tahun20' => 850000000,
                'deleted' => 0,
                'create_date' => now(),
                'update_date' => now(),
            ],
            [
                'nama_pemukiman' => 'Apartemen Sky Garden',
                'alamat_pemukiman' => 'Jl. Sky Garden Tower, Jakarta Selatan',
                'kode_lokasi' => 'SG003',
                'kode_hunian' => 'SG-C3',
                'tipe_hunian' => 'Apartemen',
                'ukuran' => '75 m²',
                'harga' => 750000000,
                'tahun5' => 800000000,
                'tahun10' => 850000000,
                'tahun15' => 900000000,
                'tahun20' => 950000000,
                'deleted' => 0,
                'create_date' => now(),
                'update_date' => now(),
            ],
            [
                'nama_pemukiman' => 'Villa Bukit Indah',
                'alamat_pemukiman' => 'Jl. Bukit Indah No. 789, Bogor',
                'kode_lokasi' => 'BI004',
                'kode_hunian' => 'BI-D4',
                'tipe_hunian' => 'Villa',
                'ukuran' => '200 m²',
                'harga' => 1200000000,
                'tahun5' => 1300000000,
                'tahun10' => 1400000000,
                'tahun15' => 1500000000,
                'tahun20' => 1600000000,
                'deleted' => 0,
                'create_date' => now(),
                'update_date' => now(),
            ],
            [
                'nama_pemukiman' => 'Townhouse Modern Living',
                'alamat_pemukiman' => 'Jl. Modern Living Complex, Tangerang',
                'kode_lokasi' => 'ML005',
                'kode_hunian' => 'ML-E5',
                'tipe_hunian' => 'Townhouse',
                'ukuran' => '110 m²',
                'harga' => 700000000,
                'tahun5' => 750000000,
                'tahun10' => 800000000,
                'tahun15' => 850000000,
                'tahun20' => 900000000,
                'deleted' => 0,
                'create_date' => now(),
                'update_date' => now(),
            ],
        ];

        foreach ($sampleData as $index => $data) {
            $existing = DataHunian::where('kode_hunian', $data['kode_hunian'])->first();

            if ($existing) {
                $this->warn("   ⚠️  Skipping {$data['nama_pemukiman']} - already exists");
                continue;
            }

            $hunian = DataHunian::create($data);
            $this->info("   ✅ Created: {$hunian->nama_pemukiman} (ID: {$hunian->id})");
        }

        $totalCount = DataHunian::count();
        $this->newLine();
        $this->info("🎯 Sample data creation completed!");
        $this->line("   Total DataHunian records: {$totalCount}");

        return 0;
    }
}
