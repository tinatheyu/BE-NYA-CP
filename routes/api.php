<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\TestimoniController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\TentangkamiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::apiResource('berita', BeritaController::class);
Route::post('/berita/{id}/update', [BeritaController::class, 'update']);
Route::patch('berita/{id}/status', [BeritaController::class, 'updateStatus']);
Route::apiResource('testimoni', TestimoniController::class);
// Route::get('/testimoni/admin', [TestimoniController::class, 'adminIndex']);
Route::prefix('admin')->group(function () {
    Route::get('/testimoni', [TestimoniController::class, 'adminIndex']);
    Route::patch('/testimoni/{id}/status', [TestimoniController::class, 'updateStatus']);
});

Route::apiResource('galeri', GaleriController::class);
Route::post('/galeri/{id}/update', [GaleriController::class, 'update']);

Route::apiResource('tentang-kami', TentangkamiController::class);
Route::apiResource('program', ProgramController::class);
Route::post('/program/{id}/update', [ProgramController::class, 'update']);
Route::get('/dashboard/count', [DashboardController::class, 'count']);

Route::get('/sso/callback', [AuthController::class, 'callback']);
Route::post('/logout', [AuthController::class, 'logout']);
