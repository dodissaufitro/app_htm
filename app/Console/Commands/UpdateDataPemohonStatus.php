<?php

namespace App\Console\Commands;

use App\Models\DataPemohon;
use App\Models\Status;
use Illuminate\Console\Command;

class UpdateDataPemohonStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-pemohon:update-status {--random : Assign random status} {--status= : Assign specific status to all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status for all DataPemohon records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $random = $this->option('random');
        $specificStatus = $this->option('status');

        if ($specificStatus) {
            $this->updateToSpecificStatus($specificStatus);
        } elseif ($random) {
            $this->assignRandomStatus();
        } else {
            $this->showCurrentDistribution();
        }

        return 0;
    }

    private function updateToSpecificStatus(string $statusCode): void
    {
        // Validate status exists
        $status = Status::where('kode', $statusCode)->first();
        if (!$status) {
            $this->error("Status '{$statusCode}' not found.");
            $this->showAvailableStatus();
            return;
        }

        // Update all records
        $count = DataPemohon::query()->update(['status_permohonan' => $statusCode]);

        $this->info("Updated {$count} DataPemohon records to status: {$status->nama_status}");
    }

    private function assignRandomStatus(): void
    {
        $statusCodes = Status::pluck('kode')->toArray();
        $dataPemohon = DataPemohon::all();
        $updated = 0;

        foreach ($dataPemohon as $record) {
            $randomStatus = $statusCodes[array_rand($statusCodes)];
            $record->update(['status_permohonan' => $randomStatus]);
            $updated++;
        }

        $this->info("Assigned random status to {$updated} DataPemohon records.");
        $this->showCurrentDistribution();
    }

    private function showCurrentDistribution(): void
    {
        $this->line('');
        $this->line('Current Status Distribution:');
        $this->line('============================');

        $distribution = DataPemohon::selectRaw('status_permohonan, COUNT(*) as count')
            ->with('status')
            ->groupBy('status_permohonan')
            ->get();

        foreach ($distribution as $item) {
            $statusName = $item->status?->nama_status ?? 'Unknown';
            $this->line("  {$item->status_permohonan}: {$statusName} ({$item->count} records)");
        }

        $total = DataPemohon::count();
        $this->line('');
        $this->info("Total DataPemohon records: {$total}");
    }

    private function showAvailableStatus(): void
    {
        $this->line('');
        $this->line('Available status codes:');

        $statusList = Status::orderBy('urut')
            ->get()
            ->map(fn($status) => "  {$status->kode}: {$status->nama_status}")
            ->join("\n");

        $this->line($statusList);
    }
}
