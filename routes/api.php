<?php

use App\Http\Controllers\Api\ScannerController;
use App\Http\Middleware\ThrottleScannerGuessing;
use Illuminate\Support\Facades\Route;

// Public (the phone has no login). Guarded against code guessing and floods.
Route::prefix('scanner')->middleware([ThrottleScannerGuessing::class, 'throttle:240,1'])->group(function () {
    Route::post('connect', [ScannerController::class, 'connect']);
    Route::post('scan', [ScannerController::class, 'scan']);
    Route::post('ping', [ScannerController::class, 'ping']);
    Route::get('status', [ScannerController::class, 'status']);
});
