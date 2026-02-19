<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\BedController;
use Illuminate\Support\Facades\Route;

Route::prefix('beds')->group(function () {
    Route::get('/', [BedController::class, 'index']);
    Route::get('/{bed}/status', [BedController::class, 'status']);
    Route::post('/{bed}/admit', [BedController::class, 'admit']);
    Route::post('/{bed}/discharge', [BedController::class, 'discharge']);
    Route::post('/{bed}/transfer', [BedController::class, 'transfer']);
});

Route::prefix('patients')->group(function () {
    Route::get('/search', [PatientController::class, 'findBed']);
});
