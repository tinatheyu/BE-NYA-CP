<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\TestimoniController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\TentangkamiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::middleware(['sso:1'])->group(function () {
    Route::apiResource('berita', BeritaController::class);
    Route::post('/berita/{id}/update', [BeritaController::class, 'update']);
    Route::patch('berita/{id}/status', [BeritaController::class, 'updateStatus']);
    Route::apiResource('testimoni', TestimoniController::class);
    Route::apiResource('galeri', GaleriController::class);
    Route::apiResource('testimoni', TestimoniController::class);
    Route::post('/galeri/{id}/update', [GaleriController::class, 'update']);
    Route::apiResource('tentang-kami', TentangkamiController::class);
    Route::apiResource('program', ProgramController::class);
    Route::post('/program/{id}/update', [ProgramController::class, 'update']);
    Route::get('/dashboard/count', [DashboardController::class, 'count']);
    Route::get('/test-service', [AuthController::class, 'testService']);
});

Route::prefix('sso')->group(function () {
    Route::get('/callback', [AuthController::class, 'callback']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/login', [AuthController::class, 'login']);
});
