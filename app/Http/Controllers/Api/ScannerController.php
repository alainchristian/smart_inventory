<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScannerSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScannerController extends Controller
{
    /**
     * Validate and activate a scanner session
     */
    public function connect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid session code format',
            ], 400);
        }

        $session = ScannerSession::active()
            ->where('session_code', $request->session_code)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found or expired',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Scanner connected successfully',
            'session' => [
                'code' => $session->session_code,
                'page_type' => $session->page_type,
                'transfer_number' => $session->transfer->transfer_number ?? null,
                'expires_at' => $session->expires_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Send scanned barcode to session
     */
    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_code' => 'required|string|size:6',
            'barcode' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors(),
            ], 400);
        }

        $session = ScannerSession::active()
            ->where('session_code', $request->session_code)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found or expired',
            ], 404);
        }

        // Record the scan
        $session->recordScan($request->barcode);

        return response()->json([
            'success' => true,
            'message' => 'Barcode sent successfully',
            'barcode' => $request->barcode,
        ]);
    }

    /**
     * Check session status and get latest scan
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid session code',
            ], 400);
        }

        $session = ScannerSession::active()
            ->where('session_code', $request->session_code)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'is_active' => true,
            'last_scanned_barcode' => $session->last_scanned_barcode,
            'last_scan_at' => $session->last_scan_at?->toIso8601String(),
        ]);
    }
}
