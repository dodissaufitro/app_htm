<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataPemohonController;
use App\Services\BapendaService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Data Pemohon API Routes
Route::prefix('data-pemohon')->group(function () {
    // Standard CRUD routes
    Route::get('/', [DataPemohonController::class, 'index']);
    Route::post('/', [DataPemohonController::class, 'store']);
    Route::get('/{identifier}', [DataPemohonController::class, 'show']);
    Route::put('/{identifier}', [DataPemohonController::class, 'update']);
    Route::patch('/{identifier}', [DataPemohonController::class, 'update']);
    Route::delete('/{identifier}', [DataPemohonController::class, 'destroy']);
});

// Bapenda API Routes
Route::prefix('bapenda')->group(function () {
    // Test API connection
    Route::get('/test-connection', function () {
        $bapendaService = app(BapendaService::class);
        $result = $bapendaService->testApiConnection();

        return response()->json($result);
    });

    // Update Bapenda data by ID
    Route::post('/update/{id}', function (Request $request, int $id) {
        $bapendaService = app(BapendaService::class);
        $result = $bapendaService->updateBapendaDataById($id);

        return response()->json($result, $result['success'] ? 200 : 422);
    })->where('id', '[0-9]+');    // Update Bapenda data by NIK
    Route::post('/update-by-nik', function (Request $request) {
        $request->validate(['nik' => 'required|string|min:16|max:16']);

        $bapendaService = app(BapendaService::class);
        $result = $bapendaService->updateBapendaDataByNik($request->nik);

        return response()->json($result, $result['success'] ? 200 : 422);
    });

    // Legacy route - Update Bapenda data by NIK (alternative method)
    Route::post('/update-by-nik-legacy', function (Request $request) {
        $request->validate(['nik' => 'required|string|min:16|max:16']);

        $dataPemohon = \App\Models\DataPemohon::where('nik', $request->nik)->first();
        if (!$dataPemohon) {
            return response()->json([
                'success' => false,
                'message' => 'Data pemohon not found'
            ], 404);
        }

        $bapendaService = app(BapendaService::class);
        $result = $bapendaService->updateBapendaDataById($dataPemohon->id);

        return response()->json($result, $result['success'] ? 200 : 422);
    });
});
