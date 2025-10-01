<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Status;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestDataPemohonTabs extends Command
{
    protected $signature = 'test:data-pemohon-tabs {email}';
    protected $description = 'Test tabs yang muncul di halaman DataPemohon untuk user tertentu';

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
        $this->line("=== DATA PEMOHON TABS TEST FOR: {$user->name} ({$user->email}) ===");

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

        // Simulate getTabs() method logic
        $this->line('');
        $this->line("TABS YANG AKAN MUNCUL:");

        // Get allowed statuses
        $statusesQuery = Status::orderBy('urut');

        if (!empty($user->allowed_status)) {
            $statusesQuery->whereIn('kode', $user->allowed_status);
        }

        $statuses = $statusesQuery->get();

        $this->line("  Jumlah status yang diizinkan: " . $statuses->count());

        // Show "Semua" tab logic
        if ($statuses->count() > 1) {
            $totalQuery = \App\Models\DataPemohon::query();
            if (!empty($user->allowed_status)) {
                $totalQuery->whereIn('status_permohonan', $user->allowed_status);
            }
            $totalCount = $totalQuery->count();

            $this->line("  📊 Tab 'Semua': {$totalCount} records");
        } else {
            $this->line("  ❌ Tab 'Semua': HIDDEN (hanya 1 status)");
        }

        // Show individual status tabs
        foreach ($statuses as $status) {
            $count = \App\Models\DataPemohon::where('status_permohonan', $status->kode)->count();
            $this->line("  📊 Tab '{$status->nama_status}' ({$status->kode}): {$count} records");
        }

        $this->line('');
        $this->line(str_repeat('=', 60));

        // Show what tabs would be hidden
        $allStatuses = Status::orderBy('urut')->get();
        $hiddenStatuses = $allStatuses->reject(function ($status) use ($user) {
            return empty($user->allowed_status) || in_array($status->kode, $user->allowed_status);
        });

        if ($hiddenStatuses->count() > 0) {
            $this->line('');
            $this->line("TABS YANG DISEMBUNYIKAN:");
            foreach ($hiddenStatuses as $status) {
                $count = \App\Models\DataPemohon::where('status_permohonan', $status->kode)->count();
                $this->line("  ❌ '{$status->nama_status}' ({$status->kode}): {$count} records");
            }
        } else {
            $this->line('');
            $this->line("✅ Tidak ada tabs yang disembunyikan (user bisa akses semua status)");
        }

        $this->line('');
        $this->info("🎉 Test selesai!");
    }
}
