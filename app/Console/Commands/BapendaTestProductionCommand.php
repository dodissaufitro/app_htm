<?php

namespace App\Console\Commands;

use App\Services\BapendaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class BapendaTestProductionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bapenda:test-production 
                            {--url= : Override API URL for testing}
                            {--timeout=5 : Connection timeout in seconds}
                            {--nik=1304081010940006 : NIK to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Bapenda API with production-like settings';

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
        $this->info('🌐 Testing Bapenda API with production settings...');
        $this->line('');

        // Override configuration for testing
        $originalUrl = config('bapenda.api_url');
        $originalTimeout = config('bapenda.timeout');

        if ($this->option('url')) {
            Config::set('bapenda.api_url', $this->option('url'));
            $this->line("📝 Using custom URL: " . $this->option('url'));
        }

        Config::set('bapenda.timeout', $this->option('timeout'));
        $this->line("⏱️  Using timeout: " . $this->option('timeout') . " seconds");
        $this->line('');

        // Test connection
        $this->info('🔍 Testing basic connection...');
        $result = $this->bapendaService->testApiConnection();

        if ($result['success']) {
            $this->info('✅ Connection successful!');
            $this->line("   Status: {$result['status_code']}");
            $this->line("   Response size: {$result['response_size']} bytes");
            $this->line('');

            // Test with actual NIK
            $nik = $this->option('nik');
            $this->info("🧪 Testing with NIK: {$nik}");

            $updateResult = $this->bapendaService->updateBapendaDataByNik($nik);

            if ($updateResult['success']) {
                $this->info('✅ Update successful!');
                $this->line("   ID: {$updateResult['id']}");
                $this->line("   ID Pendaftaran: {$updateResult['id_pendaftaran']}");
            } else {
                $this->error('❌ Update failed!');
                $this->line("   Error: {$updateResult['message']}");
            }
        } else {
            $this->error('❌ Connection failed!');

            if (isset($result['error'])) {
                $this->line("   Error: {$result['error']}");
            }

            if (isset($result['status_code'])) {
                $this->line("   Status: {$result['status_code']}");
            }

            $this->line('');
            $this->line('💡 Suggestions:');
            $this->line('   1. Check if you\'re on the same network as the API server');
            $this->line('   2. Try with VPN if API is on internal network');
            $this->line('   3. Ask for external/public API endpoint');
            $this->line('   4. Use mock mode for development: php artisan bapenda:mock enable');
        }

        // Restore original configuration
        Config::set('bapenda.api_url', $originalUrl);
        Config::set('bapenda.timeout', $originalTimeout);

        return $result['success'] ? self::SUCCESS : self::FAILURE;
    }
}
