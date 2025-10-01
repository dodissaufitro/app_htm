<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestAllAccessControl extends Command
{
    protected $signature = 'test:all-access-control {email}';
    protected $description = 'Comprehensive test untuk semua aspek access control';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User '{$email}' not found.");
            return 1;
        }

        $this->line('');
        $this->line("🎯 COMPREHENSIVE ACCESS CONTROL TEST");
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

        // Test 1: Navigation Badges
        $this->info("1️⃣  TESTING NAVIGATION BADGES");
        $this->line("───────────────────────────────────────────────────────────────");
        $this->call('test:all-navigation-badges', ['email' => $email]);

        $this->line('');

        // Test 2: DataPemohon Tabs
        $this->info("2️⃣  TESTING DATAPEMOHON TABS");
        $this->line("───────────────────────────────────────────────────────────────");
        $this->call('test:data-pemohon-tabs', ['email' => $email]);

        $this->line('');
        $this->line("═══════════════════════════════════════════════════════════════");
        $this->info("🎉 COMPREHENSIVE TEST COMPLETED!");
        $this->line("═══════════════════════════════════════════════════════════════");
    }
}
