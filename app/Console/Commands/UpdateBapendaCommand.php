<?php

namespace App\Console\Commands;

use App\Services\BapendaService;
use App\Models\DataPemohon;
use Illuminate\Console\Command;

class UpdateBapendaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bapenda:update 
                            {--id= : Update data for specific pemohon ID}
                            {--nik= : Update data for specific NIK}
                            {--all : Update data for all pemohon}
                            {--missing : Update data only for pemohon without bapenda data}
                            {--limit=50 : Limit number of records to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Bapenda data from API for data_pemohon table';

    private BapendaService $bapendaService;

    public function __construct(BapendaService $bapendaService)
    {
        parent::__construct();
        $this->bapendaService = $bapendaService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Bapenda data update...');

        try {
            // Update by specific ID
            if ($this->option('id')) {
                return $this->updateById((int) $this->option('id'));
            }

            // Update by specific NIK
            if ($this->option('nik')) {
                return $this->updateByNik($this->option('nik'));
            }

            // Update all or missing
            if ($this->option('all') || $this->option('missing')) {
                return $this->updateMultiple();
            }

            // No specific option provided
            $this->error('Please specify an option: --id, --nik, --all, or --missing');
            $this->line('');
            $this->line('Examples:');
            $this->line('  php artisan bapenda:update --id=123');
            $this->line('  php artisan bapenda:update --nik=1234567890123456');
            $this->line('  php artisan bapenda:update --missing');
            $this->line('  php artisan bapenda:update --all --limit=10');

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("Command failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * Update data by ID
     */
    private function updateById(int $id): int
    {
        $this->info("Updating Bapenda data for pemohon ID: {$id}");

        $result = $this->bapendaService->updateBapendaDataById($id);

        if ($result['success']) {
            $this->info("✅ Success: {$result['message']}");
            $this->line("   NIK: {$result['nik']}");
            $this->line("   ID Pendaftaran: {$result['id_pendaftaran']}");
            return self::SUCCESS;
        } else {
            $this->error("❌ Failed: {$result['message']}");
            return self::FAILURE;
        }
    }

    /**
     * Update data by NIK
     */
    private function updateByNik(string $nik): int
    {
        $this->info("Updating Bapenda data for NIK: {$nik}");

        $result = $this->bapendaService->updateBapendaDataByNik($nik);

        if ($result['success']) {
            $this->info("✅ Success: {$result['message']}");
            $this->line("   ID: {$result['id']}");
            $this->line("   ID Pendaftaran: {$result['id_pendaftaran']}");

            // Show summary if available
            if (isset($result['summary'])) {
                $summary = $result['summary'];
                $this->line("   📊 Summary:");
                $this->line("      - Updated at: {$summary['updated_at']}");
                $this->line("      - Pemohon vehicles: {$summary['pemohon']['vehicle_count']['total']} (Roda2: {$summary['pemohon']['vehicle_count']['roda2']}, Roda4: {$summary['pemohon']['vehicle_count']['roda4']})");

                if ($summary['pasangan']['nik2']) {
                    $this->line("      - Pasangan NIK: {$summary['pasangan']['nik2']}");
                    $this->line("      - Pasangan has data: " . ($summary['pasangan']['has_bapenda_data'] ? 'Yes' : 'No'));
                }
            }

            return self::SUCCESS;
        } else {
            $this->error("❌ Failed: {$result['message']}");
            return self::FAILURE;
        }
    }
    /**
     * Update multiple records
     */
    private function updateMultiple(): int
    {
        $limit = (int) $this->option('limit');
        $missingOnly = $this->option('missing');

        $this->info("Updating Bapenda data for multiple pemohon...");
        $this->line("Missing only: " . ($missingOnly ? 'Yes' : 'No'));
        $this->line("Limit: {$limit} records");

        // Build query
        $query = DataPemohon::whereNotNull('nik')
            ->where('nik', '!=', '');

        if ($missingOnly) {
            $query->where(function ($q) {
                $q->whereNull('bapenda')
                    ->orWhere('bapenda', '');
            });
        }

        $pemohonList = $query->limit($limit)->get(['id', 'nik', 'nama']);

        if ($pemohonList->isEmpty()) {
            $this->info("No pemohon found for update.");
            return self::SUCCESS;
        }

        $this->info("Found " . $pemohonList->count() . " pemohon to process.");

        if ($pemohonList->count() > 10) {
            if (!$this->confirm("This will process " . $pemohonList->count() . " records. Continue?")) {
                $this->info("Operation cancelled.");
                return self::SUCCESS;
            }
        }

        // Create progress bar
        $progressBar = $this->output->createProgressBar($pemohonList->count());
        $progressBar->start();

        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ($pemohonList as $pemohon) {
            $result = $this->bapendaService->updateBapendaDataById($pemohon->id);

            if ($result['success']) {
                $successful++;
            } else {
                $failed++;
                $errors[] = "ID {$pemohon->id} ({$pemohon->nama}): {$result['message']}";
            }

            $progressBar->advance();

            // Add delay to avoid overwhelming the API
            if ($pemohonList->count() > 1) {
                sleep(1);
            }
        }

        $progressBar->finish();
        $this->line('');

        // Show summary
        $this->info("📊 Summary:");
        $this->line("   Total processed: " . $pemohonList->count());
        $this->line("   Successful: {$successful}");
        $this->line("   Failed: {$failed}");

        // Show errors if any
        if (!empty($errors)) {
            $this->line('');
            $this->error("❌ Errors:");
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->line("   {$error}");
            }

            if (count($errors) > 10) {
                $this->line("   ... and " . (count($errors) - 10) . " more errors");
            }
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
