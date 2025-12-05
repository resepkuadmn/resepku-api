<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResepController;
use App\Http\Controllers\Api\TipsController;
use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\RequestResepController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\VisitorController;


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Bisa Diakses Siapa Saja / Tamu)
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// note: password reset endpoints are handled by ForgotPasswordController below

// Forgot Password Flow
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

// Resep (Read Only)
Route::get('/resep', [ResepController::class, 'index']);
Route::get('/resep/{id}', [ResepController::class, 'show']);

// Tips (Read Only) - INI YANG KITA PINDAHKAN KELUAR
Route::get('/tips', [TipsController::class, 'index']);
Route::get('/tips/{id}', [TipsController::class, 'show']);

// About (Read Only) - INI JUGA DIPINDAHKAN
Route::get('/about', [AboutController::class, 'index']);
Route::get('/about/{id}', [AboutController::class, 'show']);

// Visitor logging (public endpoint — will attach user info when request is authenticated)
// Accept public POSTs — controller will inspect bearer token if present and classify visitor
Route::post('/visit', [VisitorController::class, 'store']);


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Hanya User Login / Admin)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // User Info & Logout
    Route::get('/user', function (Request $request) { return $request->user(); });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard Admin
    Route::get('/admin/dashboard-stats', [DashboardController::class, 'index']);

    // Request Resep
    Route::post('/request', [RequestResepController::class, 'store']); // User kirim
    Route::get('/admin/request', [RequestResepController::class, 'index']); // Admin liat
    Route::put('/admin/request/{id}', [RequestResepController::class, 'updateStatus']); // Admin aksi
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read', [NotificationController::class, 'markAsRead']);
    

    // CRUD RESEP (Admin Only)
    Route::post('/resep', [ResepController::class, 'store']);
    Route::post('/resep/{id}', [ResepController::class, 'update']); // Pakai POST untuk update file
    Route::delete('/resep/{id}', [ResepController::class, 'destroy']);

    // CRUD TIPS (Admin Only)
    Route::post('/tips', [TipsController::class, 'store']);
    Route::post('/tips/{id}', [TipsController::class, 'update']);
    Route::delete('/tips/{id}', [TipsController::class, 'destroy']);

    // CRUD ABOUT (Admin Only)
    Route::post('/about', [AboutController::class, 'store']);
    Route::post('/about/{id}', [AboutController::class, 'update']);
    Route::delete('/about/{id}', [AboutController::class, 'destroy']);

});