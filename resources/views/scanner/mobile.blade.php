<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('tenant.name') }} Scanner</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 1.25rem 1rem;
            text-align: center;
        }
        .header h1 { font-size: 1.25rem; font-weight: 700; }
        .header p  { font-size: 0.8rem; opacity: 0.8; margin-top: 2px; }

        .content { flex: 1; padding: 1.25rem 1rem; display: flex; flex-direction: column; gap: 1rem; }

        /* Connect panel */
        .connect-panel { background: #1e293b; border-radius: 1rem; padding: 1.25rem; }
        .connect-panel h2 { font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; }
        .code-input {
            width: 100%; padding: 0.85rem; font-size: 1.75rem; font-weight: 700;
            text-align: center; letter-spacing: 0.4em; text-transform: uppercase;
            background: #0f172a; border: 2px solid #334155; border-radius: 0.5rem;
            color: white; outline: none;
        }
        .code-input:focus { border-color: #6366f1; }
        .btn {
            width: 100%; padding: 0.9rem; border: none; border-radius: 0.6rem;
            font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 0.75rem;
            transition: opacity 0.2s;
        }
        .btn:active { opacity: 0.8; }
        .btn-connect   { background: #4f46e5; color: white; }
        .btn-scan      { background: #10b981; color: white; font-size: 1.2rem; padding: 1.1rem; }
        .btn-disconnect { background: #ef4444; color: white; }
        .btn-manual    { background: #475569; color: white; font-size: 0.9rem; padding: 0.7rem; }

        /* Connected panel */
        .connected-panel { display: none; background: #1e293b; border-radius: 1rem; padding: 1.25rem; }
        .session-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .session-badge {
            background: #064e3b; border: 1px solid #10b981; border-radius: 0.4rem;
            padding: 0.3rem 0.7rem; font-size: 0.8rem; color: #10b981;
        }
        .session-code { font-family: monospace; font-size: 1.1rem; font-weight: 700; letter-spacing: 0.15em; }

        /* Scan area */
        .scan-area { text-align: center; }
        .scan-icon { font-size: 4rem; margin: 0.5rem 0; }
        .scan-label { font-size: 0.9rem; color: #94a3b8; margin-bottom: 1rem; }

        /* Hidden file input */
        #camera-input {
            position: absolute; opacity: 0; width: 1px; height: 1px; overflow: hidden;
        }

        /* Feedback */
        .feedback {
            border-radius: 0.75rem; padding: 1rem; text-align: center;
            font-size: 1rem; font-weight: 600; display: none;
        }
        .feedback.success { background: #064e3b; border: 1px solid #10b981; color: #6ee7b7; }
        .feedback.warning { background: #451a03; border: 1px solid #f59e0b; color: #fcd34d; }
        .feedback.error   { background: #450a0a; border: 1px solid #ef4444; color: #fca5a5; }
        .feedback-icon { display: inline-flex; vertical-align: -3px; margin-right: 6px; }

        /* Stats */
        .stats { display: flex; gap: 0.75rem; }
        .stat {
            flex: 1; background: #0f172a; border-radius: 0.6rem;
            padding: 0.75rem; text-align: center;
        }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #6ee7b7; }
        .stat-label { font-size: 0.7rem; color: #64748b; margin-top: 2px; }

        /* Manual entry */
        .manual-section { background: #1e293b; border-radius: 1rem; padding: 1rem; display: none; }
        .manual-section h3 { font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.6rem; }
        .manual-input {
            width: 100%; padding: 0.75rem; background: #0f172a; border: 1px solid #334155;
            border-radius: 0.5rem; color: white; font-size: 1rem; outline: none;
        }
        .manual-input:focus { border-color: #6366f1; }
        .manual-row { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
        .btn-send { flex: 1; background: #4f46e5; color: white; border: none; border-radius: 0.5rem; padding: 0.65rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; }

        /* Processing overlay */
        .processing {
            position: fixed; inset: 0; background: rgba(0,0,0,0.85);
            display: none; align-items: center; justify-content: center;
            flex-direction: column; gap: 1rem; z-index: 100;
        }
        .spinner {
            width: 48px; height: 48px; border: 4px solid #334155;
            border-top-color: #6366f1; border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <div class="header">
        <h1 style="display:flex;align-items:center;justify-content:center;gap:8px">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            {{ config('tenant.name') }}
        </h1>
        <p id="header-status">Mobile Scanner</p>
    </div>

    <!-- Hidden native camera input -->
    <input type="file" id="camera-input" accept="image/*" capture="environment">

    <div class="content">

        <!-- Connect Panel -->
        <div class="connect-panel" id="connect-panel">
            <h2 style="display:flex;align-items:center;gap:8px">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                Enter Session Code
            </h2>
            <input type="text" class="code-input" id="code-input" maxlength="6" placeholder="ABC123" autocomplete="off" autocorrect="off" spellcheck="false">
            <button class="btn btn-connect" onclick="connectScanner()">Connect</button>
        </div>

        <!-- Connected Panel -->
        <div class="connected-panel" id="connected-panel">
            <div class="session-info">
                <div>
                    <div class="session-badge">● Connected</div>
                    <div class="session-code" id="session-code-display" style="margin-top:4px;">------</div>
                </div>
                <button class="btn btn-disconnect" style="width:auto;padding:0.5rem 1rem;margin-top:0;" onclick="disconnectScanner()">Disconnect</button>
            </div>

            <!-- Main scan button -->
            <div class="scan-area">
                <div class="scan-icon" style="display:flex;justify-content:center;color:#94a3b8">
                    <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                </div>
                <div class="scan-label">Tap the button below to scan a barcode</div>
                <button class="btn btn-scan" onclick="triggerCameraCapture()" style="display:flex;align-items:center;justify-content:center;gap:8px">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    Scan Barcode
                </button>
            </div>

            <!-- Feedback -->
            <div class="feedback" id="feedback" style="margin-top:0.75rem;"></div>

            <!-- Stats -->
            <div class="stats" style="margin-top:0.75rem;">
                <div class="stat">
                    <div class="stat-value" id="scan-count">0</div>
                    <div class="stat-label">Scanned</div>
                </div>
                <div class="stat">
                    <div class="stat-value" id="last-code" style="font-size:0.85rem;">—</div>
                    <div class="stat-label">Last Code</div>
                </div>
            </div>
        </div>

        <!-- Manual entry toggle -->
        <button class="btn btn-manual" id="manual-toggle" onclick="toggleManual()" style="display:none;align-items:center;justify-content:center;gap:8px">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Type Barcode Manually
        </button>
        <div class="manual-section" id="manual-section">
            <h3>MANUAL ENTRY</h3>
            <input type="text" class="manual-input" id="manual-input" placeholder="Type barcode here…" autocomplete="off">
            <div class="manual-row">
                <button class="btn-send" onclick="submitManual()">Send →</button>
            </div>
        </div>

    </div>

    <!-- Processing overlay -->
    <div class="processing" id="processing">
        <div class="spinner"></div>
        <p style="color:#94a3b8;font-size:0.9rem;">Decoding barcode…</p>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        let sessionCode     = null;
        let isConnected     = false;
        let scanCount       = 0;
        let heartbeat       = null;
        let csrfToken       = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const FEEDBACK_ICONS = {
            success: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
            warning: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            error:   '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
        };

        // ── Native camera input handler ───────────────────────────────────────
        document.getElementById('camera-input').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            document.getElementById('processing').style.display = 'flex';

            try {
                const html5QrCode = new Html5Qrcode('__hidden_decoder__');

                // Create hidden div for decoder if not exists
                if (!document.getElementById('__hidden_decoder__')) {
                    const div = document.createElement('div');
                    div.id = '__hidden_decoder__';
                    div.style.display = 'none';
                    document.body.appendChild(div);
                }

                const result = await html5QrCode.scanFile(file, false);
                console.log('📦 Decoded from photo:', result);
                await sendBarcode(result);

            } catch (err) {
                console.error('Decode failed:', err);
                showFeedback('Could not read barcode. Try again with better lighting.', 'error');
            } finally {
                document.getElementById('processing').style.display = 'none';
                // Reset input so same file can be re-selected
                e.target.value = '';
            }
        });

        function triggerCameraCapture() {
            if (!isConnected) return;
            document.getElementById('camera-input').click();
        }

        // ── Send to server ────────────────────────────────────────────────────
        async function sendBarcode(barcode) {
            try {
                const response = await fetch('/api/scanner/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ session_code: sessionCode, barcode })
                });
                const data = await response.json();
                if (data.success) {
                    scanCount++;
                    document.getElementById('scan-count').textContent = scanCount;
                    document.getElementById('last-code').textContent = barcode.length > 10
                        ? barcode.substring(0, 10) + '…' : barcode;
                    showFeedback(barcode, 'success');
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    playBeep();
                } else {
                    showFeedback(data.message || 'Error', 'warning');
                    if (data.message && data.message.includes('expired')) {
                        alert('Session expired. Please reconnect.'); disconnectScanner();
                    }
                }
            } catch (err) {
                console.error('API error:', err);
                showFeedback('Network error. Check connection.', 'error');
            }
        }

        function showFeedback(message, type) {
            const el = document.getElementById('feedback');
            el.innerHTML = '';
            const iconSpan = document.createElement('span');
            iconSpan.className = 'feedback-icon';
            iconSpan.innerHTML = FEEDBACK_ICONS[type] || FEEDBACK_ICONS.error;
            el.appendChild(iconSpan);
            el.appendChild(document.createTextNode(message));
            el.className = 'feedback ' + (type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'error');
            el.style.display = 'block';
            setTimeout(() => { el.style.display = 'none'; }, 3000);
        }

        function playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator(); const gain = ctx.createGain();
                osc.connect(gain); gain.connect(ctx.destination);
                osc.frequency.value = 880; osc.type = 'sine';
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.15);
            } catch(e) {}
        }

        // ── Connection ────────────────────────────────────────────────────────
        async function connectScanner() {
            const code = document.getElementById('code-input').value.trim().toUpperCase();
            if (code.length !== 6) { alert('Please enter a 6-character code'); return; }
            try {
                const r = await fetch('/api/scanner/connect', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ session_code: code })
                });
                const data = await r.json();
                if (data.success) {
                    sessionCode = code; isConnected = true;
                    document.getElementById('connect-panel').style.display = 'none';
                    document.getElementById('connected-panel').style.display = 'block';
                    document.getElementById('manual-toggle').style.display = 'flex';
                    document.getElementById('session-code-display').textContent = code;
                    document.getElementById('header-status').textContent =
                        (data.session.transfer_number || data.session.page_type || 'Connected');
                    startHeartbeat();
                } else { alert(data.message || 'Failed to connect'); }
            } catch(e) { alert('Connection failed. Check your internet.'); }
        }

        function startHeartbeat() {
            heartbeat = setInterval(() => {
                if (!isConnected || !sessionCode) return;
                fetch('/api/scanner/ping', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ session_code: sessionCode })
                }).catch(() => {});
            }, 5000);
        }

        function disconnectScanner() {
            clearInterval(heartbeat);
            isConnected = false; sessionCode = null; scanCount = 0;
            document.getElementById('connected-panel').style.display = 'none';
            document.getElementById('manual-toggle').style.display = 'none';
            document.getElementById('manual-section').style.display = 'none';
            document.getElementById('connect-panel').style.display = 'block';
            document.getElementById('code-input').value = '';
            document.getElementById('scan-count').textContent = '0';
            document.getElementById('last-code').textContent = '—';
            document.getElementById('header-status').textContent = 'Mobile Scanner';
        }

        function toggleManual() {
            const s = document.getElementById('manual-section');
            s.style.display = s.style.display === 'none' ? 'block' : 'none';
        }

        function submitManual() {
            const v = document.getElementById('manual-input').value.trim();
            if (v) { sendBarcode(v); document.getElementById('manual-input').value = ''; }
        }

        // ── Init ──────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const inp = document.getElementById('code-input');
            inp.addEventListener('input', e => { e.target.value = e.target.value.toUpperCase(); });
            inp.addEventListener('keypress', e => { if (e.key === 'Enter') connectScanner(); });
            document.getElementById('manual-input').addEventListener('keypress', e => {
                if (e.key === 'Enter') submitManual();
            });
            const params = new URLSearchParams(window.location.search);
            const code = params.get('code');
            if (code && code.length === 6) {
                inp.value = code.toUpperCase();
                setTimeout(() => connectScanner(), 500);
            }
        });
    </script>
</body>
</html>
