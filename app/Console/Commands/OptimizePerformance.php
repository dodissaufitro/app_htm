<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-performance {--force : Force optimization even in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize application performance by clearing caches and running optimizations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting performance optimization...');

        // Clear all caches
        $this->info('📦 Clearing caches...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('filament:clear-cached-components');

        // Optimize for production
        if (app()->isProduction() || $this->option('force')) {
            $this->info('⚡ Optimizing for production...');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
        }

        $this->info('✅ Performance optimization completed!');

        // Show recommendations
        $this->showRecommendations();

        return 0;
    }

    private function showRecommendations()
    {
        $this->newLine();
        $this->info('💡 Performance Recommendations:');
        $this->line('- Use pagination for large datasets');
        $this->line('- Add database indexes for frequently queried columns');
        $this->line('- Use eager loading to prevent N+1 queries');
        $this->line('- Monitor slow queries in logs');
        $this->line('- Consider using Redis for session and cache storage');
        $this->newLine();
    }
}
