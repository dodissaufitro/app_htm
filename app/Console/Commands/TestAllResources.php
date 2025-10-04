<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestAllResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:all-resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all Filament resources for count() errors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing All Filament Resources...');
        $this->newLine();

        $errors = [];

        // Test DataPemohonResource
        try {
            $this->line('Testing DataPemohonResource...');
            $badge = \App\Filament\Resources\DataPemohonResource::getNavigationBadge();
            $this->info("✅ DataPemohonResource badge: {$badge}");
        } catch (\Exception $e) {
            $errors[] = "DataPemohonResource: " . $e->getMessage();
            $this->error("❌ DataPemohonResource error: " . $e->getMessage());
        }

        // Test PersetujuanResource
        try {
            $this->line('Testing PersetujuanResource...');
            $badge = \App\Filament\Resources\PersetujuanResource::getNavigationBadge();
            $this->info("✅ PersetujuanResource badge: {$badge}");
        } catch (\Exception $e) {
            $errors[] = "PersetujuanResource: " . $e->getMessage();
            $this->error("❌ PersetujuanResource error: " . $e->getMessage());
        }

        // Test KelengkapanDataResource
        try {
            $this->line('Testing KelengkapanDataResource...');
            $badge = \App\Filament\Resources\KelengkapanDataResource::getNavigationBadge();
            $this->info("✅ KelengkapanDataResource badge: {$badge}");
        } catch (\Exception $e) {
            $errors[] = "KelengkapanDataResource: " . $e->getMessage();
            $this->error("❌ KelengkapanDataResource error: " . $e->getMessage());
        }

        // Test StatusResource
        try {
            $this->line('Testing StatusResource...');
            $badge = \App\Filament\Resources\StatusResource::getNavigationBadge();
            $this->info("✅ StatusResource badge: {$badge}");
        } catch (\Exception $e) {
            $errors[] = "StatusResource: " . $e->getMessage();
            $this->error("❌ StatusResource error: " . $e->getMessage());
        }

        // Test DaftarBankResource
        try {
            $this->line('Testing DaftarBankResource...');
            $badge = \App\Filament\Resources\DaftarBankResource::getNavigationBadge();
            $this->info("✅ DaftarBankResource badge: {$badge}");
        } catch (\Exception $e) {
            $errors[] = "DaftarBankResource: " . $e->getMessage();
            $this->error("❌ DaftarBankResource error: " . $e->getMessage());
        }

        $this->newLine();

        if (empty($errors)) {
            $this->info('🎉 All resources tested successfully! No count() errors found.');
        } else {
            $this->error('❌ Found ' . count($errors) . ' error(s):');
            foreach ($errors as $error) {
                $this->line("  • {$error}");
            }
        }
    }
}
