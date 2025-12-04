<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\TestimoniController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\TentangkamiController;
use App\Http\Controllers\AuthController;



            Route::apiResource('berita', App\Http\Controllers\BeritaController::class);
            Route::apiResource('testimoni', App\Http\Controllers\TestimoniController::class);
            Route::apiResource('galeri', App\Http\Controllers\GaleriController::class);
            Route::apiResource('tentang-kami', App\Http\Controllers\TentangkamiController::class);
            Route::apiResource('program', App\Http\Controllers\ProgramController::class);
            Route::patch('berita/{id}/status', [App\Http\Controllers\BeritaController::class, 'updateStatus']);
            Route::get('/sso/callback', [AuthController::class, 'callback']);


