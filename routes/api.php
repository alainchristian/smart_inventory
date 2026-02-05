<?php

use App\Http\Controllers\Api\ScannerController;
use Illuminate\Support\Facades\Route;

Route::prefix('scanner')->group(function () {
    Route::post('connect', [ScannerController::class, 'connect']);
    Route::post('scan', [ScannerController::class, 'scan']);
    Route::get('status', [ScannerController::class, 'status']);
});
