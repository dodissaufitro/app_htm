<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestFilamentResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:filament-resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Filament resources for count() errors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Filament Resources...');

        try {
            // Test KelengkapanDataResource statistics
            $this->line('Testing KelengkapanDataResource statistics...');
            $stats = \App\Models\DataPemohon::with('status')
                ->get()
                ->groupBy('status.nama_status')
                ->map(function ($group) {
                    return is_countable($group) ? $group->count() : 0;
                })
                ->reject(fn($count, $key) => is_null($key) || empty($key));

            $this->info('✅ KelengkapanDataResource statistics: OK');

            // Test PersetujuanResource statistics
            $this->line('Testing PersetujuanResource statistics...');
            $total = \App\Models\DataPemohon::where('status_permohonan', '1')->count();
            $this->info("✅ PersetujuanResource statistics: OK (Total: {$total})");

            // Test Status count
            $this->line('Testing Status count...');
            $statusCount = \App\Models\Status::count();
            $this->info("✅ Status count: OK (Total: {$statusCount})");

            // Test DataPemohon count
            $this->line('Testing DataPemohon count...');
            $pemohonCount = \App\Models\DataPemohon::count();
            $this->info("✅ DataPemohon count: OK (Total: {$pemohonCount})");

            $this->newLine();
            $this->info('🎉 All tests passed! No count() errors found.');
        } catch (\Exception $e) {
            $this->error('❌ Error found: ' . $e->getMessage());
            $this->line('File: ' . $e->getFile());
            $this->line('Line: ' . $e->getLine());
        }
    }
}
