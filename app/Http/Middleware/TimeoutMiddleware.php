<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $timeout = 300): Response
    {
        // Set memory limit dan execution time untuk operasi berat
        ini_set('memory_limit', config('performance.memory_limit', '256M'));
        set_time_limit($timeout);

        // Set database timeout
        if (config('database.default') === 'mysql') {
            DB::statement('SET SESSION wait_timeout = ?', [$timeout]);
            DB::statement('SET SESSION interactive_timeout = ?', [$timeout]);
        }

        return $next($request);
    }
}
