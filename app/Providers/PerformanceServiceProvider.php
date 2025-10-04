<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class PerformanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Prevent lazy loading untuk menghindari N+1 problem
        Model::preventLazyLoading(!app()->isProduction());

        // Set default timeout untuk database
        if (config('database.default') === 'mysql') {
            DB::statement('SET SESSION sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO"');
        }

        // Log slow queries dalam development
        if (!app()->isProduction()) {
            DB::listen(function ($query) {
                if ($query->time > 1000) { // Query lebih dari 1 detik
                    logger()->warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time,
                    ]);
                }
            });
        }

        // Optimisasi memory untuk model besar
        Model::preventAccessingMissingAttributes(!app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());
    }
}
