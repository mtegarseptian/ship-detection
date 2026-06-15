<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModelAIController;
use App\Http\Controllers\DetectionController;
use App\Http\Controllers\UserController;

// ── Auth ──────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login',   [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Protected ─────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Model AI
    Route::resource('models', ModelAIController::class)->except(['edit', 'update', 'show']);
    Route::patch('/models/{model}/toggle', [ModelAIController::class, 'toggleStatus'])
         ->name('models.toggle');

    // Deteksi
    Route::resource('detections', DetectionController::class)->only(['index', 'create', 'store', 'show']);

    // User Management (admin only)
    Route::middleware(['can:admin'])->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'destroy']);
    });
});