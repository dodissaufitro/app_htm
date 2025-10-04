<?php

namespace App\Console\Commands;

use App\Services\BapendaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BapendaDebugCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bapenda:debug 
                            {--test-connection : Test basic connection}
                            {--test-nik= : Test with specific NIK}
                            {--check-config : Check configuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug Bapenda API issues';

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
        $this->info('🔧 Bapenda API Debug Tool');
        $this->line('');

        if ($this->option('check-config')) {
            $this->checkConfiguration();
        }

        if ($this->option('test-connection')) {
            $this->testConnection();
        }

        if ($this->option('test-nik')) {
            $this->testWithNik($this->option('test-nik'));
        }

        // If no specific option, run all tests
        if (!$this->option('check-config') && !$this->option('test-connection') && !$this->option('test-nik')) {
            $this->checkConfiguration();
            $this->line('');
            $this->testConnection();
            $this->line('');
            $this->testWithNik('1304081010940006');
        }

        return self::SUCCESS;
    }

    private function checkConfiguration(): void
    {
        $this->info('📋 Configuration Check:');

        $config = [
            'api_url' => config('bapenda.api_url'),
            'client_id' => config('bapenda.client_id'),
            'username' => config('bapenda.username'),
            'timeout' => config('bapenda.timeout'),
            'retry_attempts' => config('bapenda.retry_attempts'),
            'retry_delay' => config('bapenda.retry_delay'),
            'log_requests' => config('bapenda.log_requests'),
        ];

        foreach ($config as $key => $value) {
            $this->line("   {$key}: " . ($value ?? 'null'));
        }

        // Check environment variables
        $this->line('');
        $this->info('🌍 Environment Variables:');
        $envVars = [
            'BAPENDA_API_URL',
            'BAPENDA_CLIENT_ID',
            'BAPENDA_USERNAME',
            'BAPENDA_TIMEOUT'
        ];

        foreach ($envVars as $envVar) {
            $value = env($envVar);
            $this->line("   {$envVar}: " . ($value ?? 'not set'));
        }
    }

    private function testConnection(): void
    {
        $this->info('🔍 Testing Basic Connection:');

        $result = $this->bapendaService->testApiConnection();

        if ($result['success']) {
            $this->info('   ✅ Connection successful!');
            $this->line("   Status: {$result['status_code']}");
            $this->line("   Response size: {$result['response_size']} bytes");
        } else {
            $this->error('   ❌ Connection failed!');
            if (isset($result['error'])) {
                $this->line("   Error: {$result['error']}");
            }
            if (isset($result['status_code'])) {
                $this->line("   Status: {$result['status_code']}");
            }
        }
    }

    private function testWithNik(string $nik): void
    {
        $this->info("🧪 Testing with NIK: {$nik}");

        try {
            $result = $this->bapendaService->updateBapendaDataByNik($nik);

            if ($result['success']) {
                $this->info('   ✅ Update successful!');
                $this->line("   ID: {$result['id']}");
                $this->line("   ID Pendaftaran: {$result['id_pendaftaran']}");

                if (isset($result['summary'])) {
                    $summary = $result['summary'];
                    $this->line("   📊 Summary:");
                    $this->line("      - Pemohon vehicles: {$summary['pemohon']['vehicle_count']['total']}");
                    $this->line("      - Has bapenda data: " . ($summary['pemohon']['has_bapenda_data'] ? 'Yes' : 'No'));
                    $this->line("      - Has aset hunian: " . ($summary['pemohon']['has_aset_hunian_data'] ? 'Yes' : 'No'));
                }
            } else {
                $this->error('   ❌ Update failed!');
                $this->line("   Error: {$result['message']}");
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Exception occurred!');
            $this->line("   Error: {$e->getMessage()}");
        }
    }
}
