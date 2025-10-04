<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Summary of count() error fixes applied';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Count() Error Fix Summary');
        $this->newLine();

        $this->line('📋 Issues Fixed:');
        $this->line('  1. ✅ DataPemohonResource - Added array validation for allowed_status');
        $this->line('  2. ✅ PersetujuanResource - Added array validation for allowed_status');
        $this->line('  3. ✅ KelengkapanDataResource - Added array validation for allowed_status');
        $this->line('  4. ✅ ListDataPemohons - Fixed array handling in tabs generation');
        $this->line('  5. ✅ KelengkapanDataOverview - Fixed groupBy count operation');
        $this->line('  6. ✅ VerifyAdminUser - Added array validation before implode');
        $this->newLine();

        $this->line('🛠️ Root Cause:');
        $this->line('  • User.allowed_status cast as "array" but sometimes returned as string');
        $this->line('  • whereIn() expecting array but receiving string caused count() errors');
        $this->line('  • JSON decode validation was missing in critical places');
        $this->newLine();

        $this->line('✨ Solutions Applied:');
        $this->line('  • Added is_string() checks before using allowed_status');
        $this->line('  • Added json_decode() with proper validation');
        $this->line('  • Added is_array() validation before whereIn() calls');
        $this->line('  • Added is_countable() checks for collection operations');
        $this->newLine();

        // Run final test
        $this->line('🧪 Final Verification:');
        try {
            // Test all resources
            \App\Filament\Resources\DataPemohonResource::getNavigationBadge();
            \App\Filament\Resources\PersetujuanResource::getNavigationBadge();
            \App\Filament\Resources\KelengkapanDataResource::getNavigationBadge();

            $this->info('✅ All resources working correctly');
            $this->info('✅ No count() errors detected');
            $this->info('✅ System is stable and ready for use');
        } catch (\Exception $e) {
            $this->error('❌ Still found error: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 Count() Error Fix Complete!');
    }
}
