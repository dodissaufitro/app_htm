<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BapendaMockModeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bapenda:mock {action : enable or disable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable Bapenda mock mode for development';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        if (!in_array($action, ['enable', 'disable'])) {
            $this->error('Action must be either "enable" or "disable"');
            return self::FAILURE;
        }

        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->error('.env file not found');
            return self::FAILURE;
        }

        $envContent = file_get_contents($envPath);
        $mockValue = $action === 'enable' ? 'true' : 'false';

        // Check if BAPENDA_MOCK_MODE already exists
        if (strpos($envContent, 'BAPENDA_MOCK_MODE=') !== false) {
            // Update existing line
            $envContent = preg_replace(
                '/BAPENDA_MOCK_MODE=.*/',
                'BAPENDA_MOCK_MODE=' . $mockValue,
                $envContent
            );
        } else {
            // Add new line
            $envContent .= "\nBAPENDA_MOCK_MODE=" . $mockValue;
        }

        file_put_contents($envPath, $envContent);

        // Clear config cache
        $this->call('config:clear');

        $this->info("✅ Bapenda mock mode has been {$action}d");
        $this->line("   BAPENDA_MOCK_MODE={$mockValue}");

        if ($action === 'enable') {
            $this->line('');
            $this->info('🧪 Mock mode is now enabled. API calls will return sample data.');
            $this->line('   Try running: php artisan bapenda:update --nik=1304081010940006');
        } else {
            $this->line('');
            $this->info('🌐 Mock mode is now disabled. API calls will use real Bapenda API.');
        }

        return self::SUCCESS;
    }
}
