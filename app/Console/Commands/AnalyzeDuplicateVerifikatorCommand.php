<?php

namespace App\Console\Commands;

use App\Models\AppVerifikator;
use App\Models\DataPemohon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeDuplicateVerifikatorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verifikator:analyze-duplicates 
                            {--export= : Export results to file (csv|json)}
                            {--status= : Filter by specific status (disetujui|ditolak|ditunda)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze duplicate records in app_verifikator table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📊 Analyzing app_verifikator table for duplicates...');
        $this->line('');

        $status = $this->option('status');
        $export = $this->option('export');

        // Get overall statistics
        $this->showOverallStats();
        $this->line('');

        // Find duplicates
        $duplicates = $this->findDuplicates($status);

        if ($duplicates->isEmpty()) {
            $this->info("✅ No duplicates found" . ($status ? " for status '{$status}'" : ""));
            return self::SUCCESS;
        }

        $this->showDuplicateAnalysis($duplicates);

        if ($export) {
            $this->exportResults($duplicates, $export);
        }

        return self::SUCCESS;
    }

    /**
     * Show overall statistics
     */
    private function showOverallStats(): void
    {
        $totalRecords = AppVerifikator::count();
        $uniquePemohon = AppVerifikator::distinct('pemohon_id')->count('pemohon_id');
        $duplicatePemohon = AppVerifikator::select('pemohon_id')
            ->groupBy('pemohon_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $this->info("📋 Overall Statistics:");
        $this->line("   Total app_verifikator records: {$totalRecords}");
        $this->line("   Unique pemohon with records: {$uniquePemohon}");
        $this->line("   Pemohon with duplicates: {$duplicatePemohon}");
        $this->line("   Duplicate records: " . ($totalRecords - $uniquePemohon));

        // Status breakdown
        $statusBreakdown = AppVerifikator::select('keputusan')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('keputusan')
            ->get();

        $this->line('');
        $this->info("📊 Status Breakdown:");
        foreach ($statusBreakdown as $status) {
            $this->line("   {$status->keputusan}: {$status->count} records");
        }
    }

    /**
     * Find duplicate records
     */
    private function findDuplicates(?string $status): \Illuminate\Support\Collection
    {
        $query = AppVerifikator::select('pemohon_id')
            ->selectRaw('COUNT(*) as record_count')
            ->selectRaw('MIN(id) as first_id')
            ->selectRaw('MAX(id) as last_id')
            ->selectRaw('MIN(created_at) as first_created')
            ->selectRaw('MAX(created_at) as last_created');

        if ($status) {
            $query->where('keputusan', $status);
        }

        return $query->groupBy('pemohon_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('record_count')
            ->get();
    }

    /**
     * Show duplicate analysis
     */
    private function showDuplicateAnalysis(\Illuminate\Support\Collection $duplicates): void
    {
        $this->warn("⚠️  Found {$duplicates->count()} pemohon with duplicate app_verifikator records:");
        $this->line('');

        foreach ($duplicates as $duplicate) {
            $pemohon = DataPemohon::find($duplicate->pemohon_id);
            $records = AppVerifikator::where('pemohon_id', $duplicate->pemohon_id)
                ->orderBy('created_at')
                ->get();

            $this->line("👤 Pemohon ID: {$duplicate->pemohon_id}" .
                ($pemohon ? " ({$pemohon->nama} - {$pemohon->id_pendaftaran})" : ""));
            $this->line("   📊 {$duplicate->record_count} duplicate records");
            $this->line("   📅 First: {$duplicate->first_created} | Last: {$duplicate->last_created}");

            foreach ($records as $index => $record) {
                $marker = $index === 0 ? '🔸' : '🔹';
                $timeAgo = $record->created_at->diffForHumans();
                $this->line("   {$marker} ID {$record->id}: {$record->keputusan} ({$timeAgo})");

                if (strlen($record->catatan) > 60) {
                    $this->line("      💬 " . substr($record->catatan, 0, 60) . "...");
                } else {
                    $this->line("      💬 {$record->catatan}");
                }
            }
            $this->line('');
        }

        $this->line("💡 Suggestions:");
        $this->line("   • Run cleanup with: php artisan verifikator:cleanup-duplicates --dry-run");
        $this->line("   • Keep latest records: php artisan verifikator:cleanup-duplicates --keep=latest");
        $this->line("   • Keep oldest records: php artisan verifikator:cleanup-duplicates --keep=oldest");
    }

    /**
     * Export results to file
     */
    private function exportResults(\Illuminate\Support\Collection $duplicates, string $format): void
    {
        $filename = 'duplicate_verifikator_analysis_' . now()->format('Y-m-d_H-i-s');

        $data = $duplicates->map(function ($duplicate) {
            $pemohon = DataPemohon::find($duplicate->pemohon_id);
            $records = AppVerifikator::where('pemohon_id', $duplicate->pemohon_id)
                ->orderBy('created_at')
                ->get();

            return [
                'pemohon_id' => $duplicate->pemohon_id,
                'pemohon_nama' => $pemohon?->nama,
                'id_pendaftaran' => $pemohon?->id_pendaftaran,
                'duplicate_count' => $duplicate->record_count,
                'first_record_id' => $duplicate->first_id,
                'last_record_id' => $duplicate->last_id,
                'first_created' => $duplicate->first_created,
                'last_created' => $duplicate->last_created,
                'records' => $records->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'keputusan' => $record->keputusan,
                        'created_at' => $record->created_at,
                        'catatan' => $record->catatan
                    ];
                })
            ];
        });

        if ($format === 'json') {
            file_put_contents(storage_path("app/{$filename}.json"), $data->toJson(JSON_PRETTY_PRINT));
            $this->info("📄 Results exported to: storage/app/{$filename}.json");
        } elseif ($format === 'csv') {
            $csvData = [];
            $csvData[] = ['Pemohon ID', 'Nama', 'ID Pendaftaran', 'Duplicate Count', 'First Record ID', 'Last Record ID', 'First Created', 'Last Created'];

            foreach ($data as $item) {
                $csvData[] = [
                    $item['pemohon_id'],
                    $item['pemohon_nama'],
                    $item['id_pendaftaran'],
                    $item['duplicate_count'],
                    $item['first_record_id'],
                    $item['last_record_id'],
                    $item['first_created'],
                    $item['last_created']
                ];
            }

            $fp = fopen(storage_path("app/{$filename}.csv"), 'w');
            foreach ($csvData as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);

            $this->info("📄 Results exported to: storage/app/{$filename}.csv");
        }
    }
}
