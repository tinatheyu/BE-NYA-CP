<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\TestimoniController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\TentangkamiController;
use App\Http\Controllers\AuthController;


Route::apiResource('berita', BeritaController::class);
Route::apiResource('testimoni', TestimoniController::class);
Route::apiResource('galeri', GaleriController::class);
Route::apiResource('tentang-kami', TentangkamiController::class);
Route::apiResource('program', ProgramController::class);
Route::patch('berita/{id}/status', [BeritaController::class, 'updateStatus']);

Route::get('/sso/callback', [AuthController::class, 'callback']);
Route::post('/logout', [AuthController::class, 'logout']);
