<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Status;
use App\Models\DataPemohon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestKelengkapanDataCards extends Command
{
    protected $signature = 'test:kelengkapan-cards {email}';
    protected $description = 'Test cards yang muncul di halaman Kelengkapan Data';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User '{$email}' not found.");
            return 1;
        }

        // Simulate login
        Auth::login($user);

        $this->line('');
        $this->line("=== KELENGKAPAN DATA CARDS TEST FOR: {$user->name} ({$user->email}) ===");

        // Show user context
        $roles = $user->roles->pluck('name')->toArray();
        $this->line('');
        $this->line("User Context:");
        $this->line("  Roles: " . (empty($roles) ? 'None' : implode(', ', $roles)));

        if (empty($user->allowed_status)) {
            $this->line("  Status Access: ALL status allowed");
        } else {
            $statusNames = Status::whereIn('kode', $user->allowed_status)
                ->pluck('nama_status')
                ->toArray();
            $this->line("  Status Access: " . implode(', ', $statusNames));
        }

        $this->line('');
        $this->line(str_repeat('=', 60));

        // Simulate card stats
        $this->line('');
        $this->line("CARDS YANG AKAN MUNCUL:");

        // Total Data Card
        $baseQuery = DataPemohon::query();
        if (!empty($user->allowed_status)) {
            $baseQuery->whereIn('status_permohonan', $user->allowed_status);
        }
        $totalCount = $baseQuery->count();
        $this->line("  📊 Total Data: {$totalCount} records");

        // Status Cards
        $statusesQuery = Status::orderBy('urut');
        if (!empty($user->allowed_status)) {
            $statusesQuery->whereIn('kode', $user->allowed_status);
        }
        $statuses = $statusesQuery->get();

        foreach ($statuses as $status) {
            $count = DataPemohon::where('status_permohonan', $status->kode)->count();
            $color = match ($status->kode) {
                'DRAFT' => 'gray',
                'PROSES' => 'warning',
                'APPROVED' => 'success',
                'REJECTED' => 'danger',
                'COMPLETED' => 'success',
                default => 'primary'
            };
            $this->line("  📊 {$status->nama_status}: {$count} records (color: {$color})");
        }

        // Additional Report Cards
        $this->line('');
        $this->line("ADDITIONAL REPORT CARDS:");

        // Bank Stats
        $bankStats = DataPemohon::selectRaw('id_bank, COUNT(*) as count')
            ->when(!empty($user->allowed_status), function ($query) use ($user) {
                $query->whereIn('status_permohonan', $user->allowed_status);
            })
            ->whereNotNull('id_bank')
            ->groupBy('id_bank')
            ->with('bank')
            ->get();

        if ($bankStats->count() > 0) {
            $topBank = $bankStats->sortByDesc('count')->first();
            $this->line("  🏦 Bank Terbanyak: {$topBank->bank->nama_bank} ({$topBank->count} pemohon)");
        } else {
            $this->line("  🏦 Bank Terbanyak: Tidak ada data");
        }

        // High Income
        $highIncomeCount = DataPemohon::where('gaji', '>=', 10000000)
            ->when(!empty($user->allowed_status), function ($query) use ($user) {
                $query->whereIn('status_permohonan', $user->allowed_status);
            })
            ->count();
        $this->line("  💰 Gaji ≥ 10 Juta: {$highIncomeCount} pemohon");

        // Couple DKI
        $coupleCount = DataPemohon::where('is_couple_dki', true)
            ->when(!empty($user->allowed_status), function ($query) use ($user) {
                $query->whereIn('status_permohonan', $user->allowed_status);
            })
            ->count();
        $this->line("  💕 Pasangan DKI: {$coupleCount} pemohon");

        $this->line('');
        $this->line(str_repeat('=', 60));

        // Show filtering capabilities
        $this->line('');
        $this->line("CARD FILTERING CAPABILITIES:");
        $this->line("  ✅ Klik 'Total Data' → Tampilkan semua data yang bisa diakses");
        $this->line("  ✅ Klik status card → Filter berdasarkan status");
        $this->line("  ✅ Klik 'Bank Terbanyak' → Filter berdasarkan bank tertentu");
        $this->line("  ✅ Klik 'Gaji ≥ 10 Juta' → Filter gaji tinggi");
        $this->line("  ✅ Klik 'Pasangan DKI' → Filter yang punya pasangan DKI");

        $this->line('');
        $this->info("🎉 Test selesai!");
    }
}
