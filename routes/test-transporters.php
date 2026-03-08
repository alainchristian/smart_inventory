<?php

use Illuminate\Support\Facades\Route;
use App\Models\Transporter;

Route::get('/test-transporters', function () {
    $transporters = Transporter::all();
    $active = Transporter::active()->get();

    return response()->json([
        'total_count' => $transporters->count(),
        'active_count' => $active->count(),
        'all_transporters' => $transporters->toArray(),
        'active_transporters' => $active->toArray(),
    ]);
});
