<?php

use App\Http\Controllers\Api\ApiKpiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('v1')->group(function () {
    // KPIs
    Route::get('/kpis', [ApiKpiController::class, 'index']);
    Route::get('/kpis/{kpi}', [ApiKpiController::class, 'show']);
    Route::get('/kpis/{kpi}/values', [ApiKpiController::class, 'values']);
    Route::post('/kpis/{kpi}/values', [ApiKpiController::class, 'storeValue']);
    Route::get('/kpis/{kpi}/analytics', [ApiKpiController::class, 'analytics']);
});
