<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerformanceController;

// Main Dashboard - Overview
Route::get('/', [PerformanceController::class, 'dashboard'])->name('dashboard');
Route::get('/dashboard', [PerformanceController::class, 'dashboard']);

// Admin Dashboard - Upload & Management
Route::get('/admin', [PerformanceController::class, 'index'])->name('admin.index');

// Detailed Analysis - View & Export
Route::get('/analisis/{periode?}', [PerformanceController::class, 'analisis'])->name('analisis.detail');
