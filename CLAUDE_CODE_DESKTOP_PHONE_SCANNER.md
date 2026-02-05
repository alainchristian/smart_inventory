# Instructions for Claude Code: Desktop + Phone Scanner Mode

## Objective
Enable users to work on desktop computer while using their phone as a dedicated barcode scanner. The phone connects by **scanning a QR code** displayed on the desktop, then scans product barcodes that appear on desktop automatically.

## Quick Visual Guide

```
┌─────────────────────────────────────────────────────────────┐
│                  SETUP FLOW (5 SECONDS)                     │
└─────────────────────────────────────────────────────────────┘

🖥️  DESKTOP                          📱  PHONE
┌─────────────────────────┐         ┌─────────────────────────┐
│ Pack Transfer           │         │                         │
│                         │         │  1. Open Camera App     │
│ [Enable Phone Scanner]  │         │                         │
│         ↓ (click)       │         │  2. Point at QR Code ← │
│                         │         │                         │
│  ┌─────────────┐       │         │  3. Tap Notification    │
│  │ ▄▄▄▄▄▄▄▄▄▄ │       │────────▶│     "Open in Browser"   │
│  │ ▄ QR CODE ▄│       │         │                         │
│  │ ▄▄▄▄▄▄▄▄▄▄ │       │         │  4. ✅ AUTO-CONNECTED!  │
│  └─────────────┘       │         │                         │
│  Code: AB3X9K          │         │  5. Camera starts       │
│  (backup if QR fails)  │         │     automatically       │
└─────────────────────────┘         └─────────────────────────┘

               ✅ CONNECTED - NOW SCAN PRODUCTS

🖥️  DESKTOP                          📱  PHONE
┌─────────────────────────┐         ┌─────────────────────────┐
│ Transfer Items:         │         │  📷 CAMERA VIEW         │
│                         │         │  ┌───────────────────┐  │
│ ⏳ Fanta - 0 boxes     │         │  │ Point at barcode  │  │
│                         │◀────────│  │                   │  │
│                         │ (scan)  │  │  ▐│││││││││▌     │  │
│                         │         │  │                   │  │
│ ┌─────────────────────┐│         │  └───────────────────┘  │
│ │ ✓ Found: Fanta      ││         │  BEEP! ✅ Sent          │
│ │ Boxes: [-] 3 [+]    ││         │                         │
│ │ [Confirm]           ││         │  Scans: 1               │
│ └─────────────────────┘│         │  Last: 1234567890       │
└─────────────────────────┘         └─────────────────────────┘
```

## Architecture Overview

**Desktop (Main Interface):**
- User works on full Pack/Receive Transfer interface
- Shows QR code or session code for phone pairing
- Receives scanned barcodes from phone
- Displays quantity confirmation popups
- Manages the entire transfer workflow

**Phone (Scanner Only):**
- Scans the QR code to pair with desktop session
- Opens camera-only scanner interface
- Sends scanned barcodes to server
- No complex UI - just camera and feedback

**Communication:**
- Laravel Broadcasting (WebSocket) for real-time communication
- Or fallback to polling if broadcasting not configured

## Prerequisites

Check if Laravel Echo/Broadcasting is already configured:
- Look for `config/broadcasting.php`
- Check `.env` for `BROADCAST_DRIVER`
- Check if Pusher or Laravel Reverb is configured

If broadcasting is NOT configured, we'll use a polling approach (simpler, no extra setup needed).

## Task 1: Create Scanner Session Model

Create migration: `database/migrations/xxxx_create_scanner_sessions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scanner_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_code', 10)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('page_type'); // 'pack_transfer' or 'receive_transfer'
            $table->foreignId('transfer_id')->constrained()->onDelete('cascade');
            $table->string('last_scanned_barcode')->nullable();
            $table->timestamp('last_scan_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index(['session_code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scanner_sessions');
    }
};
```

Run migration:
```bash
php artisan migrate
```

## Task 2: Create ScannerSession Model

Create file: `app/Models/ScannerSession.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ScannerSession extends Model
{
    protected $fillable = [
        'session_code',
        'user_id',
        'page_type',
        'transfer_id',
        'last_scanned_barcode',
        'last_scan_at',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'last_scan_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('session_code', $code)->exists());

        return $code;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function recordScan(string $barcode): void
    {
        $this->update([
            'last_scanned_barcode' => $barcode,
            'last_scan_at' => now(),
        ]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('expires_at', '>', now());
    }
}
```

## Task 3: Create Scanner API Controller

Create file: `app/Http/Controllers/Api/ScannerController.php`

```php
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
```

## Task 4: Add API Routes

In `routes/api.php`, add:

```php
use App\Http\Controllers\Api\ScannerController;

Route::prefix('scanner')->group(function () {
    Route::post('connect', [ScannerController::class, 'connect']);
    Route::post('scan', [ScannerController::class, 'scan']);
    Route::get('status', [ScannerController::class, 'status']);
});
```

## Task 5: Create Mobile Scanner View

Create file: `resources/views/scanner/mobile.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Scanner - Smart Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/camera-scanner.js'])
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #000;
        }
        .scanner-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
        }
        .status-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            text-align: center;
            flex-shrink: 0;
        }
        .camera-view {
            flex: 1;
            position: relative;
            background: #000;
        }
        .scan-feedback {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(34, 197, 94, 0.9);
            color: white;
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            font-size: 1.25rem;
            font-weight: bold;
            display: none;
            z-index: 1000;
        }
        .controls {
            background: rgba(0, 0, 0, 0.8);
            padding: 1rem;
            text-align: center;
            flex-shrink: 0;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-disconnect {
            background: #ef4444;
            color: white;
        }
        .connecting-message {
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="scanner-container">
        <!-- Status Bar -->
        <div class="status-bar">
            <div id="status-connected" style="display: none;">
                <p class="text-sm opacity-75 mb-1">✓ Connected to Session</p>
                <p class="text-2xl font-bold" id="session-code">------</p>
                <p class="text-xs opacity-75 mt-2">Scanning for: <span id="transfer-info">Transfer</span></p>
            </div>
            <div id="status-connecting">
                <div class="connecting-message">
                    <p class="text-lg font-semibold mb-2">📷 Scan QR Code from Desktop</p>
                    <p class="text-sm opacity-75">Point your phone camera at the QR code</p>
                    <p class="text-sm opacity-75 mt-1">shown on your desktop screen</p>
                </div>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.2);">
                    <p class="text-xs opacity-75 mb-2">Or enter code manually:</p>
                    <input type="text" 
                           id="code-input" 
                           maxlength="6"
                           placeholder="6-DIGIT CODE"
                           style="padding: 0.5rem; font-size: 1.25rem; text-align: center; letter-spacing: 0.3em; text-transform: uppercase; width: 12rem; border-radius: 0.5rem; border: none;">
                    <button onclick="connectScanner()" class="btn" style="margin-top: 0.5rem; background: white; color: #667eea;">Connect</button>
                </div>
            </div>
        </div>

        <!-- Camera View -->
        <div class="camera-view">
            <div id="barcode-scanner-container" style="width: 100%; height: 100%;">
                <video style="width: 100%; height: 100%; object-fit: cover;"></video>
                <canvas class="drawingBuffer" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></canvas>
            </div>
            <div class="scan-feedback" id="scan-feedback">✓ Scanned!</div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <div id="stats" style="color: white; font-size: 0.875rem; margin-bottom: 0.5rem;">
                Scans: <span id="scan-count">0</span> | Last: <span id="last-barcode">-</span>
            </div>
            <button onclick="disconnectScanner()" class="btn btn-disconnect">Disconnect</button>
        </div>
    </div>

    <script>
        let sessionCode = null;
        let isConnected = false;
        let scanCount = 0;
        let csrfToken = '{{ csrf_token() }}';

        // Check if code is in URL (from QR code scan)
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const codeFromUrl = urlParams.get('code');
            
            if (codeFromUrl) {
                // Auto-connect if code is in URL
                document.getElementById('code-input').value = codeFromUrl;
                connectScanner();
            }
        });

        async function connectScanner() {
            const code = document.getElementById('code-input').value.trim().toUpperCase();
            
            if (code.length !== 6) {
                alert('Please enter a 6-digit code');
                return;
            }

            // Show connecting message
            const connectingDiv = document.getElementById('status-connecting');
            connectingDiv.innerHTML = '<p class="text-lg connecting-message">Connecting...</p>';

            try {
                const response = await fetch('/api/scanner/connect', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ session_code: code })
                });

                const data = await response.json();

                if (data.success) {
                    sessionCode = code;
                    isConnected = true;
                    
                    document.getElementById('status-connecting').style.display = 'none';
                    document.getElementById('status-connected').style.display = 'block';
                    document.getElementById('session-code').textContent = sessionCode;
                    document.getElementById('transfer-info').textContent = data.session.transfer_number || data.session.page_type;
                    
                    // Start camera
                    startCamera();
                } else {
                    alert(data.message || 'Failed to connect. Please check the code and try again.');
                    // Reset the connecting screen
                    location.reload();
                }
            } catch (error) {
                console.error('Connection error:', error);
                alert('Connection failed. Please check your internet connection and try again.');
                location.reload();
            }
        }

        function startCamera() {
            if (window.BarcodeScanner) {
                window.BarcodeScanner.start('#barcode-scanner-container');
            }
        }

        function stopCamera() {
            if (window.BarcodeScanner) {
                window.BarcodeScanner.stop();
            }
        }

        async function sendBarcode(barcode) {
            if (!isConnected || !sessionCode) return;

            try {
                const response = await fetch('/api/scanner/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        session_code: sessionCode,
                        barcode: barcode
                    })
                });

                const data = await response.json();

                if (data.success) {
                    scanCount++;
                    document.getElementById('scan-count').textContent = scanCount;
                    document.getElementById('last-barcode').textContent = barcode;
                    showScanFeedback();
                } else {
                    if (data.message.includes('expired')) {
                        alert('Session expired. Please reconnect.');
                        disconnectScanner();
                    }
                }
            } catch (error) {
                console.error('Scan error:', error);
            }
        }

        function showScanFeedback() {
            const feedback = document.getElementById('scan-feedback');
            feedback.style.display = 'block';
            setTimeout(() => {
                feedback.style.display = 'none';
            }, 1000);
        }

        function disconnectScanner() {
            stopCamera();
            isConnected = false;
            sessionCode = null;
            scanCount = 0;
            
            document.getElementById('status-connected').style.display = 'none';
            document.getElementById('status-connecting').style.display = 'block';
            document.getElementById('code-input').value = '';
            document.getElementById('scan-count').textContent = '0';
            document.getElementById('last-barcode').textContent = '-';
        }

        // Listen for barcode scans
        window.addEventListener('DOMContentLoaded', () => {
            // Override the default Livewire dispatch to our custom handler
            const originalDispatch = window.Livewire?.dispatch;
            
            document.addEventListener('barcode-scanned', (event) => {
                const barcode = event.detail?.barcode || event.detail;
                if (barcode && isConnected) {
                    sendBarcode(barcode);
                }
            });
        });

        // Auto-capitalize code input
        document.addEventListener('DOMContentLoaded', () => {
            const codeInput = document.getElementById('code-input');
            codeInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.toUpperCase();
            });
            
            // Connect on Enter key
            codeInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    connectScanner();
                }
            });
        });
    </script>
</body>
</html>
```

## Task 6: Add Scanner Route

In `routes/web.php`, add:

```php
Route::get('/scanner', function () {
    return view('scanner.mobile');
})->name('scanner.mobile');
```

## Task 7: Update Pack Transfer Livewire Component

Update the PackTransfer component to add scanner session management.

Add to `app/Livewire/Inventory/Transfers/PackTransfer.php` (or wherever your component is):

```php
use App\Models\ScannerSession;

// Add properties
public ?ScannerSession $scannerSession = null;
public $showScannerQR = false;

// Add method to generate scanner session
public function generateScannerSession()
{
    // Deactivate any existing sessions for this transfer
    ScannerSession::where('transfer_id', $this->transfer->id)
        ->where('page_type', 'pack_transfer')
        ->where('user_id', auth()->id())
        ->update(['is_active' => false]);

    // Create new session
    $this->scannerSession = ScannerSession::create([
        'session_code' => ScannerSession::generateCode(),
        'user_id' => auth()->id(),
        'page_type' => 'pack_transfer',
        'transfer_id' => $this->transfer->id,
        'is_active' => true,
        'expires_at' => now()->addHours(2), // Session expires in 2 hours
    ]);

    $this->showScannerQR = true;
}

public function closeScannerSession()
{
    if ($this->scannerSession) {
        $this->scannerSession->deactivate();
        $this->scannerSession = null;
    }
    $this->showScannerQR = false;
}

// Add method to check for new scans (polling approach)
public function checkForScans()
{
    if (!$this->scannerSession) return;

    $this->scannerSession->refresh();

    if ($this->scannerSession->last_scanned_barcode && 
        $this->scannerSession->last_scan_at->isAfter(now()->subSeconds(3))) {
        
        // New scan detected
        $barcode = $this->scannerSession->last_scanned_barcode;
        
        // Clear the barcode to avoid re-processing
        $this->scannerSession->update(['last_scanned_barcode' => null]);
        
        // Process the scan (your existing scan logic)
        $this->scanInput = $barcode;
        $this->scanProduct();
    }
}

public function mount(Transfer $transfer)
{
    // ... existing mount code ...
    
    // Check for active scanner session
    $this->scannerSession = ScannerSession::active()
        ->where('transfer_id', $transfer->id)
        ->where('page_type', 'pack_transfer')
        ->where('user_id', auth()->id())
        ->first();
        
    if ($this->scannerSession) {
        $this->showScannerQR = true;
    }
}
```

## Task 8: Update Pack Transfer Blade View with Scanner Session UI

Add this section to the pack transfer blade view (before the existing scanner section):

```blade
<!-- Remote Scanner Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Phone Scanner Mode</h3>
            <p class="text-sm text-gray-600">Use your phone as a dedicated scanner</p>
        </div>
        
        @if(!$showScannerQR)
            <button type="button" 
                    wire:click="generateScannerSession"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
                Enable Phone Scanner
            </button>
        @else
            <button type="button" 
                    wire:click="closeScannerSession"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">
                Disable Scanner
            </button>
        @endif
    </div>

    @if($showScannerQR && $scannerSession)
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-6">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- QR Code Section (PRIMARY) -->
                <div class="text-center">
                    <div class="bg-white p-6 rounded-lg inline-block shadow-lg border-4 border-indigo-200">
                        {!! QrCode::size(250)->generate(route('scanner.mobile') . '?code=' . $scannerSession->session_code) !!}
                    </div>
                    <div class="mt-4 bg-indigo-100 rounded-lg p-3">
                        <p class="text-sm font-bold text-indigo-900">📱 PRIMARY METHOD</p>
                        <p class="text-xs text-indigo-700 mt-1">Open phone camera and point at QR code above</p>
                    </div>
                </div>

                <!-- Instructions Section -->
                <div class="flex flex-col justify-center">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg p-4 mb-4">
                        <p class="font-bold mb-3 text-lg">📷 Quick Setup:</p>
                        <ol class="text-sm space-y-2">
                            <li class="flex items-start">
                                <span class="font-bold mr-2">1.</span>
                                <span>Open <strong>phone camera app</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-2">2.</span>
                                <span>Point at <strong>QR code</strong> (left)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-2">3.</span>
                                <span>Tap notification to open scanner</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-2">4.</span>
                                <span><strong>Done!</strong> Start scanning barcodes</span>
                            </li>
                        </ol>
                    </div>
                    
                    <!-- Alternative Manual Code -->
                    <div class="bg-white border-2 border-gray-200 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-2">Alternative (if camera doesn't work):</p>
                        <p class="text-xs text-gray-600 mb-2">Go to: <strong class="text-indigo-600">{{ url('/scanner') }}</strong></p>
                        <p class="text-xs text-gray-600 mb-2">Enter code:</p>
                        <div class="bg-gray-50 px-4 py-2 rounded border border-gray-300">
                            <p class="text-2xl font-bold text-gray-700 tracking-widest text-center">
                                {{ $scannerSession->session_code }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 text-xs text-gray-500 text-center">
                        ⏱️ Session expires: {{ $scannerSession->expires_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Polling for scans -->
        <div wire:poll.2s="checkForScans"></div>
    @endif
</div>
```

Note: For QR code generation, you'll need to install the package:
```bash
composer require simplesoftwareio/simple-qrcode
```

Or if you prefer not to use QR codes, just remove that section and keep only the manual code entry.

## Task 9: Apply Same Updates to Receive Transfer

Repeat Tasks 7 and 8 for the ReceiveTransfer Livewire component:
- Add scanner session properties and methods
- Update the blade view with scanner session UI
- Change `page_type` to `'receive_transfer'`
- Keep polling interval at 2 seconds

## Task 10: Create User Guide

Create file: `docs/PHONE_SCANNER_MODE.md`

```markdown
# Phone Scanner Mode - User Guide

## Overview

Phone Scanner Mode allows you to work on your desktop computer while using your phone as a dedicated barcode scanner. This provides the best of both worlds:

- **Desktop**: Full interface with all details and controls
- **Phone**: Simple camera-only scanner that sends data to desktop

## How to Use

### Setup (One Time Per Transfer)

1. **On Desktop:**
   - Login to Smart Inventory
   - Navigate to Pack Transfer or Receive Transfer page
   - Click **"Enable Phone Scanner"** button
   - A QR code will appear on screen

2. **On Phone:**
   - Open your **phone camera app** (not browser yet!)
   - **Point camera at the QR code** on desktop screen
   - Phone will show notification: "Open in Safari/Chrome"
   - **Tap the notification**
   - Scanner page opens automatically and connects!
   - Camera starts automatically - ready to scan!

**Alternative (if QR doesn't work):**
   - Manually open browser and go to: `your-site.com/scanner`
   - Enter the 6-digit code shown on desktop
   - Click "Connect"

3. **Done!**
   - Phone camera is running
   - Point at product barcodes to scan
   - Scans appear on desktop automatically

### Daily Use

1. **Desktop**: Open Pack/Receive Transfer page
2. **Desktop**: Click "Enable Phone Scanner"
3. **Phone**: Point camera at QR code → Auto-connects!
4. **Phone**: Scan product barcodes
5. **Desktop**: Confirm quantities and manage transfer
6. When done, click "Disable Scanner" on desktop

## Features

✅ **Desktop shows everything** - Full transfer interface on big screen
✅ **Phone is just camera** - Simple, distraction-free scanning
✅ **Real-time sync** - Scans appear on desktop within 2 seconds
✅ **Visual feedback** - Green flash and beep on successful scan
✅ **Session security** - 6-digit codes, 2-hour expiration
✅ **Disconnect anytime** - Both sides can end session

## Tips

### For Best Results:
- **Use QR code** - It's the fastest way (5 seconds vs 30 seconds typing)
- Point phone camera at QR code from 20-30cm distance
- Wait for notification popup before tapping
- If QR doesn't work, use manual code as backup
- Keep phone and desktop on same WiFi for best performance
- Good lighting improves scan accuracy
- Hold barcode 10-20cm from phone camera
- Wait for green flash before moving to next item

### QR Code Troubleshooting:

**QR code not scanning:**
- Move phone closer/farther (try 20-30cm)
- Make sure QR code is fully visible on screen
- Try tilting phone slightly
- Ensure good lighting on desktop screen
- Clean phone camera lens
- Use the phone's **default camera app** (not browser)

**No notification appears after scanning QR:**
- Phone might have blocked the notification
- Manually type URL shown on desktop: `your-site.com/scanner`
- Then enter the 6-digit code

### General Troubleshooting:

**Scans not appearing on desktop:**
- Check scanner session is active (green indicator)
- Refresh desktop page
- Reconnect phone scanner

**Phone says "Session Expired":**
- Desktop needs to click "Enable Phone Scanner" again
- Scan QR code again or enter new code

**Camera won't start on phone:**
- Allow camera permissions in browser
- Try Chrome or Safari
- Refresh page

## Session Details

- **Duration**: 2 hours (auto-expires)
- **Polling**: Desktop checks for scans every 2 seconds
- **Security**: Unique codes per session
- **Multi-user**: Each user gets their own session

## Advantages Over Phone-Only Mode

| Phone Scanner Mode | Phone-Only Mode |
|-------------------|-----------------|
| Work on desktop | Work on phone |
| Big screen for details | Small screen |
| Easy typing/clicking | Difficult typing |
| Phone is just camera | Phone does everything |
| Professional workflow | Mobile-only workflow |

## When to Use Each Mode

**Use Phone Scanner Mode when:**
- Working in office/warehouse with desktop available
- Need to see full interface clearly
- Managing complex transfers with many items
- Want fastest, most comfortable workflow

**Use Phone-Only Mode when:**
- No desktop computer available
- Working in field or remote location
- Quick, simple transfers
- Only have phone with you
```

## Task 11: Install QR Code Package (Optional)

If you want QR code functionality:

```bash
composer require simplesoftwareio/simple-qrcode
```

If not installed, the QR code section in the blade will show an error. You can either:
1. Install the package, OR
2. Remove the QR code div and only use manual code entry

## Task 12: Update .env (if needed)

If using production, ensure CSRF is properly configured:

```env
SESSION_DOMAIN=your-domain.com
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

## Summary

After completing these tasks:

1. ✅ Desktop shows "Enable Phone Scanner" button
2. ✅ Clicking it generates a 6-digit code
3. ✅ Phone can connect using the code
4. ✅ Phone scans barcodes and sends to server
5. ✅ Desktop polls every 2 seconds for new scans
6. ✅ Desktop receives scans and shows quantity popups
7. ✅ Both devices can disconnect at any time

The system uses polling (checking every 2 seconds) which works reliably without WebSocket setup. If you want real-time (instant) updates, configure Laravel Broadcasting and modify the desktop component to listen for broadcast events instead of polling.

## Testing Checklist

- [ ] Desktop shows scanner enable button
- [ ] Clicking generates code
- [ ] Phone can access /scanner page
- [ ] Phone can connect with code
- [ ] Phone camera starts automatically
- [ ] Scanning on phone works
- [ ] Desktop receives scans within 2 seconds
- [ ] Quantity popup appears on desktop
- [ ] Disconnect works from both sides
- [ ] Session expires after 2 hours
