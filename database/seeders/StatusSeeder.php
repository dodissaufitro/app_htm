<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing status data
        Status::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $statusData = [
            [
                'kode' => '-1',
                'urut' => -1,
                'kode_urut' => -1,
                'nama_status' => 'Tidak lolos Verifikasi',
                'keterangan' => 'Tidak lolos verifikasi administrasi, informasi lebih lanjut hubungi wa center UPDP 081220122211'
            ],
            [
                'kode' => '0',
                'urut' => 0,
                'kode_urut' => 0,
                'nama_status' => 'Ditunda Bank',
                'keterangan' => 'Mohon untuk melengkapi berkas.'
            ],
            [
                'kode' => '1',
                'urut' => 1,
                'kode_urut' => 1,
                'nama_status' => 'Ditunda Verifikator',
                'keterangan' => 'Ditunda (Mohon melengkapi data pendaftaran), untuk informasi lebih lanjut dapat menghubungi WA Center UPDP (081220122211)'
            ],
            [
                'kode' => '2',
                'urut' => 2,
                'kode_urut' => 2,
                'nama_status' => 'Approval Pengembang/ Developer',
                'keterangan' => 'Lolos verifikasi administrasi (oleh UPDP), untuk informasi lebih lanjut dapat cek email (sesuai data pendaftaran) untuk undangan survey lokasi dan proses bank'
            ],
            [
                'kode' => '3',
                'urut' => 3,
                'kode_urut' => 3,
                'nama_status' => 'Ditolak',
                'keterangan' => 'Tidak lolos verifikasi administrasi (oleh UPDP), untuk informasi lebih lanjut dapat menghubungi WA Center UPDP (081220122211)'
            ],
            [
                'kode' => '4',
                'urut' => 4,
                'kode_urut' => 4,
                'nama_status' => 'Dibatalkan',
                'keterangan' => 'Sesuai informasi pelaku pembangunan saudara menyatakan mengundurkan diri sehingga tidak diproses lebih lanjut'
            ],
            [
                'kode' => '5',
                'urut' => 5,
                'kode_urut' => 5,
                'nama_status' => 'Administrasi Bank',
                'keterangan' => 'Anda telah survey, bila berminat silahkan menghubungi pihak perbankan'
            ],
            [
                'kode' => '6',
                'urut' => 6,
                'kode_urut' => 6,
                'nama_status' => 'Ditunda Developer',
                'keterangan' => 'Ditunda (menunggu kelengkapan data pendaftaran), untuk info lebih lanjut dapat menghubungi pelaku pembangunan sesuai pilihan lokasi'
            ],
            [
                'kode' => '8',
                'urut' => 8,
                'kode_urut' => 8,
                'nama_status' => 'Tidak lolos analisa perbankan',
                'keterangan' => 'Tidak Lolos analisa perbankan, informasi lebih lanjut hubungi pihak perbankan'
            ],
            [
                'kode' => '9',
                'urut' => 9,
                'kode_urut' => 9,
                'nama_status' => 'Bank',
                'keterangan' => 'Lolos administrasi perbankan dan menunggu proses penetapan'
            ],
            [
                'kode' => '10',
                'urut' => 10,
                'kode_urut' => 10,
                'nama_status' => 'Akad Kredit',
                'keterangan' => 'Menunggu penjadwalan penandatanganan akad kredit'
            ],
            [
                'kode' => '11',
                'urut' => 11,
                'kode_urut' => 11,
                'nama_status' => 'BAST',
                'keterangan' => 'Telah melakukan proses penandatanganan Akad Kredit (Segera hubungi Pelaku Pembangunan untuk proses Serah Terima unit)'
            ],
            [
                'kode' => '12',
                'urut' => 12,
                'kode_urut' => 12,
                'nama_status' => 'Selesai',
                'keterangan' => 'Selamat anda telah menjadi Penerima Manfaat Fasilitas Pembiayaan Perolehan Rumah dari Pemprov DKI Jakarta'
            ],
            [
                'kode' => '15',
                'urut' => 15,
                'kode_urut' => 15,
                'nama_status' => 'Verifikasi Dokumen Pendaftaran',
                'keterangan' => 'Menunggu verifikasi administrasi (oleh UPDP)'
            ],
            [
                'kode' => '16',
                'urut' => 16,
                'kode_urut' => 16,
                'nama_status' => 'Tahap Survey',
                'keterangan' => 'Anda sudah masuk tahapan survey, silahkan hubungi pelaku pembangunan'
            ],
            [
                'kode' => '17',
                'urut' => 17,
                'kode_urut' => 17,
                'nama_status' => 'Penetapan',
                'keterangan' => 'Menunggu hasil analisis perbankan (ketika upload dokumen sudah di lengkapi)'
            ],
            [
                'kode' => '18',
                'urut' => 18,
                'kode_urut' => 18,
                'nama_status' => 'Pengajuan Dibatalkan',
                'keterangan' => 'Pengajuan Dibatalkan'
            ],
            [
                'kode' => '19',
                'urut' => 19,
                'kode_urut' => 19,
                'nama_status' => 'Verifikasi Dokumen Pendaftaran',
                'keterangan' => 'Swasana'
            ],
            [
                'kode' => '20',
                'urut' => 20,
                'kode_urut' => 20,
                'nama_status' => 'Ditunda Penetapan',
                'keterangan' => 'Ditunda Penetapan'
            ],
        ];

        foreach ($statusData as $data) {
            Status::create($data);
        }
    }
}
