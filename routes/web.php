<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersetujuanController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Routes untuk persetujuan
    Route::prefix('persetujuan')->name('persetujuan.')->group(function () {
        Route::get('/pemohon', [PersetujuanController::class, 'pemohon'])->name('pemohon');
        Route::get('/search-by-nik', [PersetujuanController::class, 'searchByNik'])->name('search-by-nik');
        Route::post('/update-bapenda', [PersetujuanController::class, 'updateBapenda'])->name('update-bapenda');
        Route::post('/update-bapenda-by-nik', [PersetujuanController::class, 'updateBapendaByNik'])->name('update-bapenda-by-nik');
        Route::post('/update-status', [PersetujuanController::class, 'updateStatus'])->name('update-status');
    });

    // Route untuk test API Bapenda
    Route::get('/test-bapenda', function () {
        $bapendaService = app(\App\Services\BapendaService::class);
        $result = $bapendaService->testApiConnection();

        return response()->json($result);
    })->name('test-bapenda');
});
