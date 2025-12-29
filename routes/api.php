<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerformanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Performance API Routes
Route::prefix('performance')->group(function () {
    // Get data with filters
    Route::get('/data', [PerformanceController::class, 'getData']);
    
    // Get available filters
    Route::get('/filters', [PerformanceController::class, 'getFilters']);
    
    // Get batches
    Route::get('/batches', [PerformanceController::class, 'getBatches']);
    
    // Get statistics
    Route::get('/stats', [PerformanceController::class, 'getStats']);
    
    // Get monthly overview (NEW)
    Route::get('/monthly-overview', [PerformanceController::class, 'getMonthlyOverview']);
    
    // Upload Excel
    Route::post('/upload', [PerformanceController::class, 'upload']);
    
    // Delete batch
    Route::delete('/batches/{batchId}', [PerformanceController::class, 'deleteBatch']);
});
