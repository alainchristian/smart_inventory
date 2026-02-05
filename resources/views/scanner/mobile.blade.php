<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mobile Scanner - Smart Inventory</title>
    @vite(['resources/css/app.css'])
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
            z-index: 100;
        }
        .camera-view {
            flex: 1;
            position: relative;
            background: #000;
            overflow: hidden;
        }
        #barcode-scanner-container {
            width: 100%;
            height: 100%;
            position: relative;
        }
        #barcode-scanner-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .scan-feedback {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(34, 197, 94, 0.95);
            color: white;
            padding: 1.5rem 2.5rem;
            border-radius: 0.75rem;
            font-size: 1.5rem;
            font-weight: bold;
            display: none;
            z-index: 1000;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }
        .controls {
            background: rgba(0, 0, 0, 0.9);
            padding: 1rem;
            text-align: center;
            flex-shrink: 0;
            z-index: 100;
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
        .btn-connect {
            background: white;
            color: #667eea;
            margin-top: 0.5rem;
        }
        .code-input {
            margin-top: 0.5rem;
            padding: 0.75rem;
            font-size: 1.75rem;
            text-align: center;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            width: 16rem;
            max-width: 90%;
            border-radius: 0.5rem;
            border: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="scanner-container">
        <!-- Status Bar -->
        <div class="status-bar">
            <div id="status-connected" style="display: none;">
                <p class="text-sm opacity-75 mb-1">Connected to Session</p>
                <p class="text-3xl font-bold" id="session-code">------</p>
                <p class="text-xs opacity-75 mt-2">📦 <span id="transfer-info">Transfer</span></p>
            </div>
            <div id="status-connecting">
                <p class="text-xl font-semibold mb-2">📱 Mobile Scanner</p>
                <p class="text-sm opacity-90 mb-3">Enter Session Code</p>
                <input type="text"
                       id="code-input"
                       maxlength="6"
                       placeholder="------"
                       class="code-input">
                <br>
                <button onclick="connectScanner()" class="btn btn-connect">
                    <span>🔗 Connect</span>
                </button>
            </div>
        </div>

        <!-- Camera View -->
        <div class="camera-view">
            <div id="barcode-scanner-container">
                <video id="scanner-video" playsinline></video>
            </div>
            <div class="scan-feedback" id="scan-feedback">✓ Scanned!</div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <div id="stats" style="color: white; font-size: 0.875rem; margin-bottom: 0.75rem;">
                Scans: <span id="scan-count" class="font-bold text-green-400">0</span> |
                Last: <span id="last-barcode" class="font-mono text-blue-300">-</span>
            </div>
            <button onclick="disconnectScanner()" class="btn btn-disconnect" style="display: none;" id="disconnect-btn">
                ⛔ Disconnect
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2@1.8.4/dist/quagga.min.js"></script>
    <script>
        let sessionCode = null;
        let isConnected = false;
        let scanCount = 0;
        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let lastScannedBarcode = null;
        let lastScanTime = 0;

        async function connectScanner() {
            const code = document.getElementById('code-input').value.trim().toUpperCase();

            if (code.length !== 6) {
                alert('Please enter a 6-digit code');
                return;
            }

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
                    document.getElementById('disconnect-btn').style.display = 'inline-block';

                    // Start camera
                    startCamera();
                } else {
                    alert(data.message || 'Failed to connect');
                }
            } catch (error) {
                console.error('Connection error:', error);
                alert('Connection failed. Please try again.');
            }
        }

        function startCamera() {
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: document.querySelector('#barcode-scanner-container'),
                    constraints: {
                        facingMode: "environment"
                    },
                },
                decoder: {
                    readers: ["ean_reader", "ean_8_reader", "code_128_reader", "code_39_reader", "upc_reader"]
                }
            }, function(err) {
                if (err) {
                    console.error('Camera error:', err);
                    alert('Failed to start camera: ' + err.message);
                    return;
                }
                Quagga.start();
            });

            Quagga.onDetected(function(result) {
                const barcode = result.codeResult.code;
                const currentTime = Date.now();

                // Debounce: only process if different from last or 2 seconds passed
                if (barcode !== lastScannedBarcode || (currentTime - lastScanTime) > 2000) {
                    lastScannedBarcode = barcode;
                    lastScanTime = currentTime;
                    sendBarcode(barcode);
                }
            });
        }

        function stopCamera() {
            if (typeof Quagga !== 'undefined') {
                Quagga.stop();
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
                    document.getElementById('last-barcode').textContent = barcode.substring(0, 15);
                    showScanFeedback();

                    // Vibrate if supported
                    if (navigator.vibrate) {
                        navigator.vibrate(100);
                    }
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
            lastScannedBarcode = null;

            document.getElementById('status-connected').style.display = 'none';
            document.getElementById('status-connecting').style.display = 'block';
            document.getElementById('code-input').value = '';
            document.getElementById('scan-count').textContent = '0';
            document.getElementById('last-barcode').textContent = '-';
            document.getElementById('disconnect-btn').style.display = 'none';
        }

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

            // Check if code provided in URL
            const urlParams = new URLSearchParams(window.location.search);
            const code = urlParams.get('code');
            if (code && code.length === 6) {
                document.getElementById('code-input').value = code.toUpperCase();
                // Auto-connect if code provided
                setTimeout(() => connectScanner(), 500);
            }
        });
    </script>
</body>
</html>
