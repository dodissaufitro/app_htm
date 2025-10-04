<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DataPemohon;
use App\Observers\DataPemohonObserver;
use App\Observers\BapendaObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Model Observers
        DataPemohon::observe(DataPemohonObserver::class);
        DataPemohon::observe(BapendaObserver::class);

        // Log semua query yang bermasalah dengan field 'status'
        DB::listen(function ($query) {
            if (str_contains($query->sql, '`status` =') || str_contains($query->sql, 'status =')) {
                Log::warning('Query detected with status field: ' . $query->sql);
                Log::warning('Bindings: ' . json_encode($query->bindings));
                Log::warning('Stack trace: ' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10));
            }
        });
    }
}
