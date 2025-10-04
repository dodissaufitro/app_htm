<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataPemohon;
use App\Models\AppVerifikator;

class TestDuplicateFix extends Command
{
    protected $signature = 'test:duplicate-fix';
    protected $description = 'Test the duplicate fix for app_verifikator';

    public function handle()
    {
        $this->info('=== Testing Duplicate Fix ===');

        // Get current counts
        $beforeCount = AppVerifikator::count();
        $this->info("App Verifikator count before: {$beforeCount}");

        // Get first data pemohon
        $dp = DataPemohon::first();
        $this->info("Current status: {$dp->status_permohonan}");

        // Update status (this should trigger Observer)
        $dp->update([
            'status_permohonan' => '3',
            'keterangan' => 'Test catatan ditolak via command'
        ]);

        // Check counts after
        $afterCount = AppVerifikator::count();
        $this->info("App Verifikator count after: {$afterCount}");

        // Check the difference
        $difference = $afterCount - $beforeCount;
        $this->info("Records created: {$difference}");

        if ($difference === 0) {
            $this->info("✅ SUCCESS: No duplicate created (updated existing record)");
        } elseif ($difference === 1) {
            $this->info("✅ SUCCESS: Only 1 new record created");
        } else {
            $this->error("❌ PROBLEM: {$difference} records created - this indicates duplication!");
        }

        // Show final status
        $this->info("Final status: {$dp->fresh()->status_permohonan}");

        $this->info('=== Test Complete ===');

        return 0;
    }
}
