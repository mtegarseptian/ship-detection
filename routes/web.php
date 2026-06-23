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

Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);

// ── Protected ─────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Deteksi (Bisa diakses oleh semua: Admin & User)
    Route::resource('detections', DetectionController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // TAMBAHKAN BARIS INI: Route untuk Hapus Semua
    Route::delete('/detections-clear/all', [DetectionController::class, 'clear'])->name('detections.clear');

    // ── HANYA ADMIN (Pengelolaan Model AI & User) ──
    Route::middleware(['can:admin'])->group(function () {
        
        // Model AI
        Route::resource('models', ModelAIController::class)->except(['edit', 'update', 'show']);
        Route::patch('/models/{model}/toggle', [ModelAIController::class, 'toggleStatus'])
             ->name('models.toggle');

        // User Management
        Route::resource('users', UserController::class)->only(['index', 'destroy']);
        
    });
});