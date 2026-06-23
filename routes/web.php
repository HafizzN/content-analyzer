<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/analyze', [DashboardController::class, 'analyze'])->name('analysis.analyze');
Route::get('/analysis/{profile}', [DashboardController::class, 'show'])->name('analysis.show');
Route::get('/analysis/{profile}/export', [DashboardController::class, 'export'])->name('analysis.export');
Route::get('/analysis/{profile}/export-planner', [DashboardController::class, 'exportPlanner'])->name('analysis.export-planner');
Route::delete('/analysis/{profile}', [DashboardController::class, 'destroy'])->name('analysis.destroy');

