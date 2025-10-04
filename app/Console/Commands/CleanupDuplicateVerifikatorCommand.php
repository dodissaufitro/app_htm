<?php

namespace App\Console\Commands;

use App\Models\AppVerifikator;
use App\Models\DataPemohon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateVerifikatorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verifikator:cleanup-duplicates 
                            {--pemohon-id= : Clean specific pemohon ID}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--keep=latest : Keep latest or oldest record (latest|oldest)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate records in app_verifikator table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧹 Cleaning up duplicate app_verifikator records...');
        $this->line('');

        $pemohonId = $this->option('pemohon-id');
        $dryRun = $this->option('dry-run');
        $keep = $this->option('keep');

        if ($pemohonId) {
            return $this->cleanupSpecificPemohon($pemohonId, $dryRun, $keep);
        } else {
            return $this->cleanupAllDuplicates($dryRun, $keep);
        }
    }

    /**
     * Clean up duplicates for specific pemohon
     */
    private function cleanupSpecificPemohon(int $pemohonId, bool $dryRun, string $keep): int
    {
        $this->info("🔍 Checking pemohon ID: {$pemohonId}");

        $duplicates = AppVerifikator::where('pemohon_id', $pemohonId)
            ->orderBy('created_at', $keep === 'latest' ? 'desc' : 'asc')
            ->get();

        if ($duplicates->count() <= 1) {
            $this->info("✅ No duplicates found for pemohon ID: {$pemohonId}");
            return self::SUCCESS;
        }

        $this->warn("⚠️  Found {$duplicates->count()} records for pemohon ID: {$pemohonId}");

        // Keep the first record (latest or oldest based on option)
        $keepRecord = $duplicates->first();
        $deleteRecords = $duplicates->skip(1);

        $this->line("📋 Records to process:");
        foreach ($duplicates as $index => $record) {
            $status = $index === 0 ? '✅ KEEP' : '❌ DELETE';
            $this->line("   {$status} ID: {$record->id}, Created: {$record->created_at}, Keputusan: {$record->keputusan}");
        }

        if (!$dryRun) {
            if (!$this->confirm("Do you want to proceed with deletion?")) {
                $this->info("Operation cancelled.");
                return self::SUCCESS;
            }

            foreach ($deleteRecords as $record) {
                $record->delete();
                $this->line("🗑️  Deleted record ID: {$record->id}");
            }

            $this->info("✅ Cleanup completed. Kept record ID: {$keepRecord->id}");
        } else {
            $this->info("🏃 Dry run mode - no records were actually deleted");
        }

        return self::SUCCESS;
    }

    /**
     * Clean up all duplicates
     */
    private function cleanupAllDuplicates(bool $dryRun, string $keep): int
    {
        $this->info("🔍 Finding all duplicate app_verifikator records...");

        // Find pemohon IDs with multiple records
        $duplicatePemohonIds = AppVerifikator::select('pemohon_id')
            ->groupBy('pemohon_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('pemohon_id');

        if ($duplicatePemohonIds->isEmpty()) {
            $this->info("✅ No duplicates found in app_verifikator table");
            return self::SUCCESS;
        }

        $this->warn("⚠️  Found duplicates for {$duplicatePemohonIds->count()} pemohon(s)");

        $totalDeleted = 0;
        $totalKept = 0;

        foreach ($duplicatePemohonIds as $pemohonId) {
            $records = AppVerifikator::where('pemohon_id', $pemohonId)
                ->orderBy('created_at', $keep === 'latest' ? 'desc' : 'asc')
                ->get();

            // Keep the first record
            $keepRecord = $records->first();
            $deleteRecords = $records->skip(1);

            $this->line("📋 Pemohon ID {$pemohonId}: {$records->count()} records");
            $this->line("   ✅ KEEP: ID {$keepRecord->id} (Created: {$keepRecord->created_at})");

            foreach ($deleteRecords as $record) {
                $this->line("   ❌ DELETE: ID {$record->id} (Created: {$record->created_at})");

                if (!$dryRun) {
                    $record->delete();
                    $totalDeleted++;
                }
            }

            $totalKept++;
        }

        $this->line('');
        $this->info("📊 Summary:");
        $this->line("   Pemohon with duplicates: {$duplicatePemohonIds->count()}");
        $this->line("   Records kept: {$totalKept}");

        if ($dryRun) {
            $this->line("   Records would be deleted: {$totalDeleted}");
            $this->info("🏃 Dry run mode - no records were actually deleted");
            $this->line("   Run without --dry-run to actually delete duplicates");
        } else {
            $this->line("   Records deleted: {$totalDeleted}");
            $this->info("✅ Cleanup completed successfully");
        }

        return self::SUCCESS;
    }
}
