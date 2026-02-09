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

        /* Scanning frame overlay */
        .scanning-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 280px;
            height: 280px;
            border: 3px solid #10b981;
            border-radius: 12px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
            pointer-events: none;
            z-index: 100;
        }

        /* Corner markers */
        .scanning-frame::before,
        .scanning-frame::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 30px;
            border: 4px solid #10b981;
        }

        .scanning-frame::before {
            top: -3px;
            left: -3px;
            border-right: none;
            border-bottom: none;
        }

        .scanning-frame::after {
            top: -3px;
            right: -3px;
            border-left: none;
            border-bottom: none;
        }

        /* Bottom corners */
        .corner-bl,
        .corner-br {
            position: absolute;
            width: 30px;
            height: 30px;
            border: 4px solid #10b981;
        }

        .corner-bl {
            bottom: -3px;
            left: -3px;
            border-right: none;
            border-top: none;
        }

        .corner-br {
            bottom: -3px;
            right: -3px;
            border-left: none;
            border-top: none;
        }

        /* Animated scanning line */
        .scan-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: linear-gradient(to right, transparent, #10b981, transparent);
            box-shadow: 0 0 10px #10b981;
            animation: scan 2s ease-in-out infinite;
        }

        @keyframes scan {
            0%, 100% { top: 0; }
            50% { top: calc(100% - 2px); }
        }

        /* Status indicator pulse */
        .status-pulse {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
            margin-right: 6px;
            vertical-align: middle;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.3); }
        }

        /* Scanning instruction */
        .scan-instruction {
            position: absolute;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
            z-index: 101;
            white-space: nowrap;
        }

        /* Active scanning indicator */
        .scanning-active {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(16, 185, 129, 0.95);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            z-index: 101;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="scanner-container">
        <!-- Status Bar -->
        <div class="status-bar">
            <div id="status-connected" style="display: none;">
                <p class="text-sm opacity-75 mb-1">🔄 Initializing Camera...</p>
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

        <!-- Test Camera Button (for debugging) -->
        <button onclick="testCamera()" style="position: fixed; top: 10px; right: 10px; z-index: 9999; padding: 10px; background: #3b82f6; color: white; border: none; border-radius: 5px; font-size: 12px; opacity: 0.8;">
            Test Camera
        </button>

        <!-- Camera View -->
        <div class="camera-view">
            <div id="barcode-scanner-container">
                <video id="scanner-video" playsinline></video>

                <!-- Scanning Frame Overlay -->
                <div class="scanning-frame" id="scanning-frame" style="display: none;">
                    <div class="corner-bl"></div>
                    <div class="corner-br"></div>
                    <div class="scan-line"></div>
                </div>

                <!-- Active Scanning Indicator -->
                <div class="scanning-active" id="scanning-active" style="display: none;">
                    <span class="status-pulse"></span>
                    <span>🔍 Scanning Active</span>
                </div>

                <!-- Scan Instruction -->
                <div class="scan-instruction" id="scan-instruction" style="display: none;">
                    📱 Point camera at barcode
                </div>
            </div>

            <!-- Scan Success Feedback -->
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
        let connectionHeartbeat = null;

        // Check if camera is supported
        function checkCameraSupport() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                return {
                    supported: false,
                    message: 'Your browser does not support camera access.\n\nPlease use:\n• iOS: Safari browser\n• Android: Chrome or Firefox'
                };
            }

            const isSecure = location.protocol === 'https:' ||
                            location.hostname === 'localhost' ||
                            location.hostname === '127.0.0.1' ||
                            /^192\.168\./.test(location.hostname) ||
                            /^10\./.test(location.hostname);

            if (!isSecure && /Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                console.warn('HTTP detected - camera may be blocked by browser security');
            }

            return { supported: true };
        }

        function showError(message) {
            const container = document.getElementById('barcode-scanner-container');
            if (container) {
                container.innerHTML = `
                    <div style="padding: 20px; background: #fee; color: #c00; text-align: center; white-space: pre-wrap; font-family: sans-serif; line-height: 1.6;">
                        <h3 style="margin-bottom: 15px; font-size: 1.5rem;">⚠️ Camera Error</h3>
                        <p style="font-size: 14px;">${message}</p>
                        <button onclick="location.reload()" style="margin-top: 20px; padding: 10px 20px; background: #ef4444; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
                            Try Again
                        </button>
                    </div>
                `;
            }
        }

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

                    // Start heartbeat to keep connection alive
                    startConnectionHeartbeat();

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

        async function startCamera() {
            // Check browser compatibility
            const support = checkCameraSupport();
            if (!support.supported) {
                showError(support.message);
                return;
            }

            // Request camera permission explicitly first
            try {
                console.log('📷 Requesting camera permission...');

                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });

                console.log('✓ Camera permission granted');

                // Stop test stream
                stream.getTracks().forEach(track => track.stop());

                // Now initialize Quagga
                initializeQuagga();

            } catch (error) {
                console.error('❌ Camera permission error:', error);

                let errorMessage = 'Failed to access camera.\n\n';

                if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                    errorMessage += '🚫 Camera permission denied.\n\n';
                    errorMessage += 'Please:\n';
                    errorMessage += '1. Tap the address bar\n';
                    errorMessage += '2. Tap the lock/camera icon\n';
                    errorMessage += '3. Set Camera to "Allow"\n';
                    errorMessage += '4. Refresh this page';
                } else if (error.name === 'NotFoundError') {
                    errorMessage += '📷 No camera detected.\n\n';
                    errorMessage += 'Please check camera is not covered\nand no other app is using it.';
                } else if (error.name === 'NotSupportedError') {
                    errorMessage += '🌐 Camera not supported.\n\n';
                    errorMessage += 'Please use:\n• iOS: Safari browser\n• Android: Chrome browser';
                } else if (error.name === 'NotReadableError') {
                    errorMessage += '⚠️ Camera is busy.\n\n';
                    errorMessage += 'Close other apps using camera\nand try again.';
                } else {
                    errorMessage += 'Error: ' + error.name + '\n' + error.message;
                }

                showError(errorMessage);
            }
        }

        function initializeQuagga() {
            console.log('🎥 Initializing barcode scanner...');

            if (typeof Quagga === 'undefined') {
                showError('Scanner library not loaded.\nPlease refresh the page.');
                return;
            }

            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: document.querySelector('#barcode-scanner-container'),
                    constraints: {
                        width: { min: 640 },
                        height: { min: 480 },
                        facingMode: "environment",
                        aspectRatio: { min: 1, max: 2 }
                    }
                },
                decoder: {
                    readers: [
                        "code_128_reader",
                        "ean_reader",
                        "ean_8_reader",
                        "code_39_reader",
                        "upc_reader",
                        "upc_e_reader"
                    ]
                },
                locate: true,
                locator: {
                    patchSize: "medium",
                    halfSample: true
                },
                numOfWorkers: 4,
                frequency: 10
            }, function(err) {
                if (err) {
                    console.error('❌ Quagga initialization error:', err);
                    showError('Failed to start scanner:\n' + err.message + '\n\nPlease refresh and try again.');
                    return;
                }

                console.log('✓ Scanner initialized successfully');
                Quagga.start();

                // Show visual indicators
                document.getElementById('scanning-frame').style.display = 'block';
                document.getElementById('scanning-active').style.display = 'flex';
                document.getElementById('scan-instruction').style.display = 'block';

                // Update status bar
                updateCameraStatus('📷 Camera Active - Scanning...', '#10b981');
            });

            Quagga.onDetected(function(result) {
                const barcode = result.codeResult.code;
                const currentTime = Date.now();

                // Debounce
                if (barcode !== lastScannedBarcode || (currentTime - lastScanTime) > 2000) {
                    console.log('📦 Barcode detected:', barcode);
                    lastScannedBarcode = barcode;
                    lastScanTime = currentTime;
                    sendBarcode(barcode);
                }
            });
        }

        function updateCameraStatus(text, color) {
            const statusBar = document.querySelector('.status-bar');
            let statusElement = document.getElementById('camera-status');

            if (!statusElement && statusBar) {
                statusElement = document.createElement('p');
                statusElement.id = 'camera-status';
                statusElement.className = 'text-xs opacity-75 mt-1';
                document.getElementById('status-connected').appendChild(statusElement);
            }

            if (statusElement) {
                statusElement.textContent = text;
                statusElement.style.color = color;
            }
        }

        function stopCamera() {
            if (typeof Quagga !== 'undefined') {
                Quagga.stop();
            }

            // Hide visual indicators
            const frame = document.getElementById('scanning-frame');
            const active = document.getElementById('scanning-active');
            const instruction = document.getElementById('scan-instruction');

            if (frame) frame.style.display = 'none';
            if (active) active.style.display = 'none';
            if (instruction) instruction.style.display = 'none';
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
                    showScanFeedback(barcode);

                    // Double vibrate if supported
                    if (navigator.vibrate) {
                        navigator.vibrate([100, 50, 100]);
                    }

                    // Play beep sound
                    playBeep();
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

        function showScanFeedback(barcode) {
            // Show feedback with barcode
            const feedback = document.getElementById('scan-feedback');
            feedback.textContent = '✓ Scanned: ' + (barcode ? barcode.substring(0, 10) + '...' : '');
            feedback.style.display = 'block';

            // Flash the scanning frame green
            const frame = document.getElementById('scanning-frame');
            if (frame) {
                frame.style.borderColor = '#22c55e';
                frame.style.boxShadow = '0 0 30px #22c55e, 0 0 0 9999px rgba(0, 0, 0, 0.5)';

                setTimeout(() => {
                    frame.style.borderColor = '#10b981';
                    frame.style.boxShadow = '0 0 0 9999px rgba(0, 0, 0, 0.5)';
                }, 300);
            }

            setTimeout(() => {
                feedback.style.display = 'none';
            }, 1500);
        }

        // Play beep sound on successful scan
        function playBeep() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 800;
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.15);
            } catch (err) {
                // Silent fail if audio not supported
                console.log('Audio beep not supported');
            }
        }

        // Connection heartbeat to keep session active
        function startConnectionHeartbeat() {
            // Send ping every 5 seconds to keep connection active
            connectionHeartbeat = setInterval(() => {
                if (isConnected && sessionCode) {
                    fetch('/api/scanner/ping', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            session_code: sessionCode
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            console.warn('Connection lost');
                            // Optionally show reconnection prompt
                        }
                    })
                    .catch(error => {
                        console.error('Heartbeat failed:', error);
                    });
                }
            }, 5000); // Every 5 seconds
        }

        function stopConnectionHeartbeat() {
            if (connectionHeartbeat) {
                clearInterval(connectionHeartbeat);
                connectionHeartbeat = null;
            }
        }

        function disconnectScanner() {
            stopConnectionHeartbeat();
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

        // Test camera function
        function testCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('✗ getUserMedia not supported\n\nYour browser does not support camera access.\n\niOS: Use Safari\nAndroid: Use Chrome');
                return;
            }

            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    alert('✓ Camera works!\n\nCamera permission granted.\nBrowser supports getUserMedia.');
                    stream.getTracks().forEach(track => track.stop());
                })
                .catch(error => {
                    alert('✗ Camera error:\n\n' + error.name + '\n' + error.message + '\n\nPlease grant camera permission in browser settings.');
                });
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
