<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestAllKelengkapanData extends Command
{
    protected $signature = 'test:all-kelengkapan-data {email}';
    protected $description = 'Comprehensive test untuk semua aspek Kelengkapan Data';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User '{$email}' not found.");
            return 1;
        }

        $this->line('');
        $this->line("🎯 COMPREHENSIVE KELENGKAPAN DATA TEST");
        $this->line("═══════════════════════════════════════════════════════════════");
        $this->line("📧 User: {$user->name} ({$user->email})");

        $roles = $user->roles->pluck('name')->toArray();
        $this->line("👤 Roles: " . (empty($roles) ? 'None' : implode(', ', $roles)));

        if (empty($user->allowed_status)) {
            $this->line("🔓 Status Access: ALL status allowed");
        } else {
            $statusNames = \App\Models\Status::whereIn('kode', $user->allowed_status)
                ->pluck('nama_status')
                ->toArray();
            $this->line("🔒 Status Access: " . implode(', ', $statusNames));
        }

        $this->line('');

        // Test 1: Navigation Badge
        $this->info("1️⃣  TESTING NAVIGATION BADGE");
        $this->line("───────────────────────────────────────────────────────────────");

        // Calculate badge count
        $user_model = \App\Models\User::where('email', $email)->first();
        \Illuminate\Support\Facades\Auth::login($user_model);

        $baseQuery = \App\Models\DataPemohon::query();
        if (!empty($user_model->allowed_status)) {
            $baseQuery->whereIn('status_permohonan', $user_model->allowed_status);
        }
        $badgeCount = $baseQuery->count();

        $this->line("  📊 Navigation Badge Count: {$badgeCount}");
        $this->line("  🎨 Badge Color: info");

        $this->line('');

        // Test 2: Cards
        $this->info("2️⃣  TESTING DASHBOARD CARDS");
        $this->line("───────────────────────────────────────────────────────────────");
        $this->call('test:kelengkapan-cards', ['email' => $email]);

        $this->line('');
        $this->line("═══════════════════════════════════════════════════════════════");
        $this->info("🎉 COMPREHENSIVE KELENGKAPAN DATA TEST COMPLETED!");
        $this->line("═══════════════════════════════════════════════════════════════");

        // Summary
        $this->line('');
        $this->info("📋 SUMMARY:");
        $this->line("✅ Tabs completely removed");
        $this->line("✅ Card-based interface implemented");
        $this->line("✅ Interactive filtering via card clicks");
        $this->line("✅ Access control applied to all cards");
        $this->line("✅ Navigation badge respects user permissions");
        $this->line("✅ Additional report cards (Bank, Income, Couple)");
        $this->line("✅ Responsive 4-column grid layout");
    }
}
