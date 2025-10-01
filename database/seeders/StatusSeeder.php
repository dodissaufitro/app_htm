<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusData = [
            [
                'kode' => 'DRAFT',
                'urut' => 1,
                'kode_urut' => 1,
                'nama_status' => 'Draft',
                'keterangan' => 'Data masih dalam tahap draft'
            ],
            [
                'kode' => 'SUBMITTED',
                'urut' => 2,
                'kode_urut' => 2,
                'nama_status' => 'Diajukan',
                'keterangan' => 'Data telah diajukan untuk review'
            ],
            [
                'kode' => 'UNDER_REVIEW',
                'urut' => 3,
                'kode_urut' => 3,
                'nama_status' => 'Sedang Ditinjau',
                'keterangan' => 'Data sedang dalam proses peninjauan'
            ],
            [
                'kode' => 'APPROVED',
                'urut' => 4,
                'kode_urut' => 4,
                'nama_status' => 'Disetujui',
                'keterangan' => 'Data telah disetujui'
            ],
            [
                'kode' => 'REJECTED',
                'urut' => 5,
                'kode_urut' => 5,
                'nama_status' => 'Ditolak',
                'keterangan' => 'Data ditolak dan perlu perbaikan'
            ],
            [
                'kode' => 'COMPLETED',
                'urut' => 6,
                'kode_urut' => 6,
                'nama_status' => 'Selesai',
                'keterangan' => 'Proses telah selesai'
            ],
        ];

        foreach ($statusData as $data) {
            Status::updateOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }
    }
}
