<?php

namespace App\Console\Commands;

use App\Services\BapendaService;
use Illuminate\Console\Command;

class TestBapendaConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bapenda:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connection to Bapenda API';

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
        $this->info('🔍 Testing Bapenda API connection...');
        $this->line('');

        $result = $this->bapendaService->testApiConnection();

        // Display configuration
        $this->line('📋 Configuration:');
        $config = $result['config_used'];
        $this->line("   API URL: {$config['api_url']}");
        $this->line("   Client ID: {$config['client_id']}");
        $this->line("   Username: {$config['username']}");
        $this->line("   Timeout: {$config['timeout']} seconds");
        $this->line('');

        if ($result['success']) {
            $this->info('✅ Connection successful!');
            $this->line("   Status Code: {$result['status_code']}");
            $this->line("   Response Size: {$result['response_size']} bytes");

            if (!empty($result['body_preview'])) {
                $this->line("   Response Preview:");
                $this->line("   " . str_replace("\n", "\n   ", $result['body_preview']));
            }

            return self::SUCCESS;
        } else {
            $this->error('❌ Connection failed!');

            if (isset($result['status_code'])) {
                $this->line("   Status Code: {$result['status_code']}");
            }

            if (isset($result['error'])) {
                $this->line("   Error: {$result['error']}");
            }

            if (isset($result['body_preview'])) {
                $this->line("   Response Preview:");
                $this->line("   " . str_replace("\n", "\n   ", $result['body_preview']));
            }

            $this->line('');
            $this->line('🔧 Troubleshooting tips:');
            $this->line('   1. Check if the API URL is accessible');
            $this->line('   2. Verify client_id and username are correct');
            $this->line('   3. Check network connectivity');
            $this->line('   4. Verify signature generation algorithm');
            $this->line('   5. Check if SSL certificate issues exist');

            return self::FAILURE;
        }
    }
}
