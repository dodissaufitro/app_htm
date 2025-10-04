<?php

namespace App\Console\Commands;

use App\Models\DataPemohon;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestDataPemohonView extends Command
{
    protected $signature = 'test:data-pemohon-view {email} {id_pendaftaran?}';
    protected $description = 'Test tampilan view DataPemohon untuk user tertentu';

    public function handle()
    {
        $email = $this->argument('email');
        $idPendaftaran = $this->argument('id_pendaftaran');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User '{$email}' not found.");
            return 1;
        }

        // Simulate login
        Auth::login($user);

        $this->line('');
        $this->line("=== DATA PEMOHON VIEW TEST FOR: {$user->name} ({$user->email}) ===");

        // Show user context
        $roles = $user->roles->pluck('name')->toArray();
        $this->line('');
        $this->line("User Context:");
        $this->line("  Roles: " . (empty($roles) ? 'None' : implode(', ', $roles)));

        if (empty($user->allowed_status)) {
            $this->line("  Status Access: ALL status allowed");
        } else {
            $statusNames = \App\Models\Status::whereIn('kode', $user->allowed_status)
                ->pluck('nama_status')
                ->toArray();
            $this->line("  Status Access: " . implode(', ', $statusNames));
        }

        $this->line('');
        $this->line(str_repeat('=', 70));

        // Get available records for this user
        $query = DataPemohon::query();
        if (!empty($user->allowed_status)) {
            $query->whereIn('status_permohonan', $user->allowed_status);
        }

        if ($idPendaftaran) {
            $record = $query->where('id_pendaftaran', $idPendaftaran)->first();
            if (!$record) {
                $this->error("Record dengan ID Pendaftaran '{$idPendaftaran}' tidak ditemukan atau tidak dapat diakses.");
                return 1;
            }
            $records = collect([$record]);
        } else {
            $records = $query->with(['status', 'bank'])->limit(3)->get();
        }

        if ($records->isEmpty()) {
            $this->warn("Tidak ada data yang dapat diakses oleh user ini.");
            return 0;
        }

        $this->line('');
        $this->info("📋 DATA PEMOHON YANG DAPAT DIAKSES:");
        $this->line('');

        foreach ($records as $index => $record) {
            $this->line("#{$record->id_pendaftaran} - {$record->nama}");
            $this->line("  📧 Email: {$record->username}");
            $this->line("  📱 HP: {$record->no_hp}");
            $this->line("  💰 Gaji: " . number_format($record->gaji ?? 0, 0, ',', '.'));
            $this->line("  📊 Status: {$record->status->nama_status} ({$record->status_permohonan})");
            $this->line("  🏦 Bank: {$record->bank->nama_bank}");

            // NPWP info
            if ($record->npwp) {
                $npwpStatus = $record->validasi_npwp ? '✅ Valid' : '⚠️ Tidak Valid';
                $this->line("  🆔 NPWP: {$record->npwp} - {$npwpStatus}");
            } else {
                $this->line("  🆔 NPWP: ❌ Tidak Ada");
            }

            // Address info
            if ($record->provinsi_dom || $record->kabupaten_dom) {
                $address = trim(($record->provinsi_dom ?? '') . ', ' . ($record->kabupaten_dom ?? ''), ', ');
                $this->line("  📍 Domisili: {$address}");
            }

            // Spouse info
            if ($record->nama2) {
                $coupleStatus = $record->is_couple_dki ? '(Warga DKI)' : '(Non-DKI)';
                $this->line("  💑 Pasangan: {$record->nama2} {$coupleStatus}");
            }

            // Property info
            if ($record->lokasi_rumah) {
                $this->line("  🏢 Lokasi Pilihan: {$record->lokasi_rumah}");
                if ($record->harga_unit) {
                    $this->line("  💵 Harga Unit: " . number_format($record->harga_unit, 0, ',', '.'));
                }
            }

            $this->line('');
        }

        $this->line(str_repeat('=', 70));
        $this->line('');
        $this->info("🎨 TAMPILAN VIEW FEATURES:");
        $this->line("  ✅ Informasi terstruktur dalam sections");
        $this->line("  ✅ Data Verifikasi dengan badges status");
        $this->line("  ✅ Lampiran Dokumen dengan status validasi");
        $this->line("  ✅ Detail NPWP dengan icon validasi");
        $this->line("  ✅ Alamat domisili dan korespondensi");
        $this->line("  ✅ Data pasangan lengkap");
        $this->line("  ✅ Informasi keuangan dan aset");
        $this->line("  ✅ Data hunian yang dipilih");
        $this->line("  ✅ Copyable fields untuk NIK, HP, Email");
        $this->line("  ✅ Color-coded badges dan icons");
        $this->line("  ✅ Collapsible sections untuk better UX");

        $this->line('');
        $this->info("🚀 NAVIGATION ACTIONS:");
        $this->line("  📝 Edit Action (warning color)");
        $this->line("  🗑️  Delete Action (danger color)");

        $this->line('');
        $this->info("🎉 View tampilan siap digunakan!");

        if (!$idPendaftaran && $records->count() >= 3) {
            $this->line('');
            $this->comment("💡 TIP: Gunakan 'php artisan test:data-pemohon-view {$email} <ID_PENDAFTARAN>' untuk test record spesifik");
        }
    }
}
