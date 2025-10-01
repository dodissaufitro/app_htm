<?php

namespace Database\Seeders;

use App\Models\DataPemohon;
use App\Models\DaftarBank;
use Illuminate\Database\Seeder;

class DataPemohonSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have some banks first
        $banks = DaftarBank::all();
        if ($banks->isEmpty()) {
            // Create some banks if they don't exist
            $bankData = [
                ['id' => 'BCA', 'nama_bank' => 'Bank Central Asia', 'kode_bank' => '014'],
                ['id' => 'BNI', 'nama_bank' => 'Bank Negara Indonesia', 'kode_bank' => '009'],
                ['id' => 'BRI', 'nama_bank' => 'Bank Rakyat Indonesia', 'kode_bank' => '002'],
                ['id' => 'MANDIRI', 'nama_bank' => 'Bank Mandiri', 'kode_bank' => '008'],
                ['id' => 'BTN', 'nama_bank' => 'Bank Tabungan Negara', 'kode_bank' => '200'],
            ];

            foreach ($bankData as $bank) {
                DaftarBank::create($bank);
            }

            $banks = DaftarBank::all();
        }

        $pemohonData = [
            [
                'id_pendaftaran' => 'REG001/2024',
                'username' => 'ahmad.sari',
                'nik' => '3171010101850001',
                'kk' => '3171011234567890',
                'nama' => 'Ahmad Sari',
                'pendidikan' => 'S1',
                'npwp' => '12.345.678.9-012.000',
                'nama_npwp' => 'Ahmad Sari',
                'validasi_npwp' => 1,
                'status_npwp' => 1,
                'no_hp' => '081234567890',
                'provinsi2_ktp' => 'DKI Jakarta',
                'kabupaten_ktp' => 'Jakarta Pusat',
                'kecamatan_ktp' => 'Menteng',
                'kelurahan_ktp' => 'Menteng',
                'provinsi_dom' => 'DKI Jakarta',
                'kabupaten_dom' => 'Jakarta Pusat',
                'kecamatan_dom' => 'Menteng',
                'kelurahan_dom' => 'Menteng',
                'alamat_dom' => 'Jl. Menteng Raya No. 123',
                'sts_rumah' => 'milik_sendiri',
                'pekerjaan' => 'Karyawan Swasta',
                'gaji' => 15000000,
                'status_kawin' => 1,
                'nik2' => '3171010101860002',
                'nama2' => 'Siti Nurhaliza',
                'no_hp2' => '081234567891',
                'pendidikan2' => 'S1',
                'pekerjaan2' => 'Guru',
                'gaji2' => 8000000,
                'is_couple_dki' => true,
                'tipe_unit' => 'Tipe 45/90',
                'harga_unit' => 450000000,
                'is_have_booking_kpr_dpnol' => false,
                'lokasi_rumah' => 'Jakarta Timur',
                'tipe_rumah' => 'Cluster',
                'nama_blok' => 'Blok A',
                'count_of_vehicle1' => 2,
                'count_of_vehicle2' => 1,
                'mounthly_expense1' => 5000000,
                'mounthly_expense2' => 2000000,
                'is_have_saving_bank' => true,
                'is_have_home_credit' => false,
                'status_permohonan' => 'pending',
                'id_bank' => $banks->random()->id,
                'bapenda' => 'Valid tax record',
                'bapenda_pasangan' => 'Valid spouse tax record',
                'bapenda_pasangan_pbb' => 'Valid spouse PBB record',
                'reason_of_choose_location' => 'Lokasi strategis dekat dengan tempat kerja dan fasilitas umum',
                'aset_hunian' => 'Residential property owned',
                'booking_files' => 'receipt001.pdf, payment001.jpg',
                'chkPengajuan' => 'on',
                'korespondensi' => 'Y',
                'atpid' => 12345,
            ],
        ];

        // Create remaining 9 records with similar pattern
        for ($i = 2; $i <= 10; $i++) {
            $names = ['Budi Santoso', 'Citra Dewi', 'Deni Kurniawan', 'Eka Putri Sari', 'Gita Savitri', 'Hendra Wijaya', 'Indah Permatasari', 'Joko Susilo', 'Kartika Sari'];
            $usernames = ['budi.santoso', 'citra.dewi', 'deni.kurniawan', 'eka.putri', 'gita.savitri', 'hendra.wijaya', 'indah.permata', 'joko.susilo', 'kartika.sari'];
            $locations = ['Jakarta Pusat', 'Jakarta Selatan', 'Jakarta Barat', 'Jakarta Utara', 'Jakarta Timur'];
            $statuses = ['pending', 'approved', 'rejected', 'review'];

            $pemohonData[] = [
                'id_pendaftaran' => sprintf('REG%03d/2024', $i),
                'username' => $usernames[$i - 2],
                'nik' => sprintf('31710101%08d', 18500000 + $i), // Fixed to exactly 16 characters
                'kk' => sprintf('31710112%08d', 34567890 + $i), // Fixed to exactly 16 characters
                'nama' => $names[$i - 2],
                'pendidikan' => ['SMA', 'D3', 'S1', 'S2'][rand(0, 3)],
                'npwp' => sprintf('12.345.678.9-0%02d.000', 10 + $i),
                'nama_npwp' => $names[$i - 2],
                'validasi_npwp' => rand(0, 1),
                'status_npwp' => rand(0, 1),
                'no_hp' => sprintf('08123456%04d', 7800 + $i), // Fixed to proper phone format
                'provinsi2_ktp' => 'DKI Jakarta',
                'kabupaten_ktp' => $locations[($i - 2) % 5],
                'kecamatan_ktp' => 'Kecamatan ' . $i,
                'kelurahan_ktp' => 'Kelurahan ' . $i,
                'provinsi_dom' => 'DKI Jakarta',
                'kabupaten_dom' => $locations[($i - 2) % 5],
                'kecamatan_dom' => 'Kecamatan ' . $i,
                'kelurahan_dom' => 'Kelurahan ' . $i,
                'alamat_dom' => 'Jl. Contoh No. ' . ($i * 100),
                'sts_rumah' => ['milik_sendiri', 'sewa', 'kontrak', 'tinggal_keluarga'][rand(0, 3)],
                'pekerjaan' => ['Karyawan Swasta', 'PNS', 'Wiraswasta', 'Professional'][rand(0, 3)],
                'gaji' => rand(5, 30) * 1000000,
                'status_kawin' => rand(0, 2),
                'is_couple_dki' => rand(0, 1),
                'tipe_unit' => 'Tipe ' . (30 + $i * 5) . '/' . (60 + $i * 10),
                'harga_unit' => (300 + $i * 50) * 1000000,
                'is_have_booking_kpr_dpnol' => rand(0, 1),
                'lokasi_rumah' => $locations[($i - 2) % 5],
                'tipe_rumah' => ['Cluster', 'Townhouse', 'Rumah Tunggal', 'Apartemen'][rand(0, 3)],
                'nama_blok' => 'Blok ' . chr(65 + ($i % 10)),
                'count_of_vehicle1' => rand(0, 3),
                'count_of_vehicle2' => rand(0, 2),
                'mounthly_expense1' => rand(2, 10) * 1000000,
                'mounthly_expense2' => rand(0, 5) * 1000000,
                'is_have_saving_bank' => rand(0, 1),
                'is_have_home_credit' => rand(0, 1),
                'status_permohonan' => $statuses[rand(0, 3)],
                'id_bank' => $banks->random()->id,
                'bapenda' => 'Valid tax record for record ' . $i,
                'bapenda_pasangan' => 'Valid spouse tax record for record ' . $i,
                'bapenda_pasangan_pbb' => 'Valid spouse PBB record for record ' . $i,
                'reason_of_choose_location' => 'Lokasi yang dipilih karena dekat dengan fasilitas untuk record ' . $i,
                'aset_hunian' => rand(0, 1) ? 'Residential property owned' : 'Residential property rented',
                'booking_files' => 'receipt00' . $i . '.pdf, payment00' . $i . '.jpg',
                'chkPengajuan' => 'on',
                'korespondensi' => rand(0, 1) ? 'Y' : 'N',
                'atpid' => 12345 + $i,
                'nik2' => rand(0, 1) ? sprintf('31710101%08d', 18600000 + $i) : null, // Fixed to 16 characters
                'nama2' => rand(0, 1) ? 'Pasangan ' . substr($names[$i - 2], 0, 15) : null, // Limit name length
                'no_hp2' => rand(0, 1) ? sprintf('08123456%04d', 7900 + $i) : null, // Fixed phone format
                'pendidikan2' => rand(0, 1) ? ['SMA', 'D3', 'S1', 'S2'][rand(0, 3)] : null,
                'pekerjaan2' => rand(0, 1) ? ['Karyawan Swasta', 'PNS', 'Wiraswasta', 'Ibu Rumah Tangga'][rand(0, 3)] : null,
                'gaji2' => rand(0, 1) ? rand(3, 20) * 1000000 : null,
            ];
        }

        foreach ($pemohonData as $data) {
            DataPemohon::create($data);
        }
    }
}
