<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SystemHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comprehensive system health check';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏥 System Health Check');
        $this->newLine();

        // Check Status table
        $statusCount = \App\Models\Status::count();
        $this->line("📊 Status Records: {$statusCount}");

        // Check Users
        $userCount = \App\Models\User::count();
        $adminUser = \App\Models\User::where('email', 'admin@gmail.com')->first();
        $this->line("👥 Users: {$userCount}");
        $this->line("🔑 Admin User: " . ($adminUser ? '✅ Found' : '❌ Missing'));

        // Check DataPemohon
        $pemohonCount = \App\Models\DataPemohon::count();
        $this->line("📝 DataPemohon Records: {$pemohonCount}");

        // Check Banks
        $bankCount = \App\Models\DaftarBank::count();
        $this->line("🏦 Bank Records: {$bankCount}");

        // Check Roles
        $roleCount = \Spatie\Permission\Models\Role::count();
        $this->line("🛡️  Roles: {$roleCount}");

        $this->newLine();

        // Test count() operations
        $this->line('🧪 Testing Count Operations...');
        try {
            // Test problematic operations
            $stats = \App\Models\DataPemohon::with('status')
                ->get()
                ->groupBy('status.nama_status')
                ->map(function ($group) {
                    return is_countable($group) ? $group->count() : 0;
                })
                ->reject(fn($count, $key) => is_null($key) || empty($key));

            $this->info('✅ All count() operations working correctly');
        } catch (\Exception $e) {
            $this->error('❌ Count error: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 System Health Check Complete!');
    }
}
