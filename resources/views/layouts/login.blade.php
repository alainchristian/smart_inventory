<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — New Shoes Ltd</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* ── Reset & base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f4f6fb;
            -webkit-font-smoothing: antialiased;
        }

        /* ════════════════════════════════
           ROOT — two-column grid
        ════════════════════════════════ */
        .login-root {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 55% 45%;
        }

        /* ════════════════════════════════
           LEFT — Brand panel
        ════════════════════════════════ */
        .brand-panel {
            position: relative;
            background: #1a1f36; /* matches --text, the app's deep navy */
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px 56px;
            overflow: hidden;
        }

        /* Accent glow at bottom-left */
        .brand-panel::before {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59,111,212,.22) 0%, transparent 70%);
            pointer-events: none;
            animation: glowPulse 6s ease-in-out infinite;
        }
        /* Subtle grid pattern */
        .brand-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 36px 36px;
            pointer-events: none;
        }

        .brand-content {
            position: relative;
            z-index: 1;
            max-width: 460px;
            animation: fadeInUp .45s .1s ease both;
        }

        /* Logo mark — uses app accent blue */
        .brand-logo {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, #3b6fd4, #5b8fe8);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
            box-shadow: 0 8px 28px rgba(59,111,212,.40);
        }
        .brand-logo svg {
            width: 30px;
            height: 30px;
            color: #fff;
        }

        .brand-name {
            font-size: 30px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            line-height: 1.1;
            margin-bottom: 6px;
        }
        .brand-tagline {
            font-size: 14.5px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 44px;
            line-height: 1.55;
        }
        .brand-tagline strong {
            color: #7aa3eb; /* lightened --accent */
            font-weight: 600;
        }

        /* Divider line in accent blue */
        .brand-divider {
            width: 36px;
            height: 3px;
            background: linear-gradient(90deg, #3b6fd4, transparent);
            border-radius: 4px;
            margin-bottom: 36px;
        }

        /* Feature list */
        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-bottom: 48px;
        }
        .brand-feature {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }
        .feature-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(59,111,212,.13);
            border: 1px solid rgba(59,111,212,.22);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .feature-icon svg {
            width: 17px;
            height: 17px;
            color: #7aa3eb;
        }
        .feature-title {
            font-size: 13.5px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 2px;
        }
        .feature-desc {
            font-size: 12px;
            color: rgba(255,255,255,.42);
            line-height: 1.5;
        }

        /* Stats row */
        .brand-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            padding-top: 36px;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .stat-value {
            font-size: 22px;
            font-weight: 800;
            color: #3b6fd4; /* --accent */
            letter-spacing: -0.5px;
            line-height: 1;
        }
        .stat-label {
            font-size: 11px;
            color: rgba(255,255,255,.38);
            margin-top: 4px;
            letter-spacing: 0.2px;
        }

        /* ════════════════════════════════
           RIGHT — Form panel
        ════════════════════════════════ */
        .form-panel {
            background: #f4f6fb; /* matches --bg */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 48px;
        }

        .form-inner {
            width: 100%;
            max-width: 380px;
        }

        /* brand-text: groups name + tagline so they stack next to the logo on mobile */
        .brand-text { /* block on desktop — no styles needed */ }

        /* Form card */
        .form-card {
            background: #ffffff; /* --surface */
            border: 1px solid #e2e6f3; /* --border */
            border-radius: 16px;
            padding: 36px 32px;
            box-shadow: 0 2px 12px rgba(26,31,54,.07), 0 8px 32px rgba(26,31,54,.05);
            animation: fadeInUp .35s ease both;
        }

        .form-eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #3b6fd4; /* --accent */
            margin-bottom: 8px;
        }
        .form-heading {
            font-size: 26px;
            font-weight: 800;
            color: #1a1f36; /* --text */
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }
        .form-subheading {
            font-size: 13.5px;
            color: #4a5372; /* --text-sub */
            margin-bottom: 28px;
        }

        /* Field */
        .field-group { margin-bottom: 18px; }
        .field-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1a1f36; /* --text */
            margin-bottom: 6px;
        }
        .field-input {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid #e2e6f3; /* --border */
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: #1a1f36;
            background: #f4f6fb; /* --bg */
            transition: border-color .15s, box-shadow .15s, background .15s;
            outline: none;
        }
        .field-input:focus {
            border-color: #3b6fd4; /* --accent */
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59,111,212,.12); /* --accent-glow */
        }
        .field-input::placeholder { color: #7a81a0; } /* --text-dim */

        .field-error {
            font-size: 12px;
            color: #e11d48; /* --red */
            margin-top: 5px;
        }

        /* Error state — field border turns red */
        .field-input.has-error {
            border-color: #e11d48;
            background: #fff;
        }
        .field-input.has-error:focus {
            border-color: #e11d48;
            box-shadow: 0 0 0 3px rgba(225,29,72,.12);
        }

        /* Autofill override — prevent browser yellow/blue backgrounds */
        .field-input:-webkit-autofill,
        .field-input:-webkit-autofill:hover,
        .field-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #1a1f36;
            -webkit-box-shadow: 0 0 0 1000px #f4f6fb inset;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* Password show/hide wrapper */
        .field-input-wrap {
            position: relative;
        }
        .field-input-wrap .field-input {
            padding-right: 42px;
        }
        .field-eye {
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #7a81a0; /* --text-dim */
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: color .15s;
            line-height: 0;
        }
        .field-eye:hover { color: #1a1f36; }
        .field-eye svg   { width: 16px; height: 16px; }

        /* Remember / forgot row */
        .form-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 8px;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: #4a5372; /* --text-sub */
            cursor: pointer;
            user-select: none;
        }
        .remember-label input[type="checkbox"] {
            width: 15px;
            height: 15px;
            border-radius: 4px;
            accent-color: #3b6fd4; /* --accent */
            cursor: pointer;
            flex-shrink: 0;
        }
        .forgot-link {
            font-size: 13px;
            font-weight: 500;
            color: #3b6fd4; /* --accent */
            text-decoration: none;
            white-space: nowrap;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* Submit button — accent blue */
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #3b6fd4; /* --accent */
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14.5px;
            font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: 0.2px;
            cursor: pointer;
            transition: background .15s, box-shadow .15s, transform .1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-login:hover {
            background: #2d5bb8;
            box-shadow: 0 4px 18px rgba(59,111,212,.35);
        }
        .btn-login:active { transform: scale(0.99); }
        .btn-login:disabled { opacity: .65; cursor: not-allowed; }

        /* Loading spinner */
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            flex-shrink: 0;
        }
        /* Spinner + label row — display:none is set by Livewire as inline style,
           which wins while hidden; when Livewire removes it this class takes effect */
        .btn-loading {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Auth status */
        .auth-status {
            padding: 10px 13px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            background: rgba(14,158,134,.07); /* --green-dim */
            color: #0e9e86; /* --green */
            border: 1px solid rgba(14,158,134,.18);
        }

        /* Footer */
        .form-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #7a81a0; /* --text-dim */
            line-height: 1.6;
        }
        .form-footer strong { color: #4a5372; }

        /* ════════════════════════════════
           KEYFRAMES
        ════════════════════════════════ */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes glowPulse {
            0%, 100% { opacity: 1;  transform: scale(1); }
            50%       { opacity: .55; transform: scale(1.1); }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ════════════════════════════════
           RESPONSIVE
        ════════════════════════════════ */

        /* Tablet: narrow the brand panel */
        @media (max-width: 1024px) {
            .login-root { grid-template-columns: 50% 50%; }
            .brand-panel { padding: 48px 40px; }
            .form-panel  { padding: 48px 32px; }
        }

        /* Small tablet / large phone: stack vertically — brand becomes a compact top banner */
        @media (max-width: 768px) {
            .login-root {
                grid-template-columns: 1fr;
                min-height: 100vh;
            }
            /* Compact horizontal banner */
            .brand-panel {
                flex-direction: row;
                align-items: center;
                justify-content: flex-start;
                padding: 20px 24px;
                min-height: unset;
            }
            .brand-content {
                max-width: 100%;
                display: flex;
                align-items: center;
                gap: 14px;
            }
            .brand-logo {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                margin-bottom: 0;
                flex-shrink: 0;
            }
            .brand-text {
                flex: 1;
                min-width: 0;
            }
            .brand-name    { font-size: 20px; margin-bottom: 1px; }
            .brand-tagline { font-size: 12px; margin-bottom: 0; }
            /* Hide verbose sections in compact mode */
            .brand-divider, .brand-features, .brand-stats { display: none; }
            /* Form below the banner */
            .form-panel {
                justify-content: flex-start;
                padding: 36px 24px 40px;
            }
            .form-inner   { max-width: 500px; }
            .form-card    { padding: 28px 24px; }
            .form-heading { font-size: 22px; }
        }

        /* Phone */
        @media (max-width: 480px) {
            .brand-panel   { padding: 16px 20px; }
            .brand-logo    { width: 38px; height: 38px; border-radius: 10px; }
            .brand-name    { font-size: 17px; }
            .brand-tagline { display: none; } /* too tight — name alone is enough */
            .form-panel    { padding: 28px 16px 32px; }
            .form-card     { padding: 24px 20px; border-radius: 14px; }
            .form-heading  { font-size: 20px; }
            .field-input   { font-size: 16px; } /* prevents iOS zoom */
            .btn-login     { font-size: 15px; padding: 13px; }
        }

        /* Tiny phones */
        @media (max-width: 360px) {
            .brand-panel { padding: 13px 16px; }
            .form-panel  { padding: 24px 12px; }
            .form-card   { padding: 20px 16px; }
        }

        /* Tall / centered on large screens — only applies to the side-column layout */
        @media (min-width: 769px) and (min-height: 900px) {
            .brand-panel, .form-panel { padding-top: 80px; padding-bottom: 80px; }
        }
    </style>
</head>
<body>
<div class="login-root">

    {{-- ══════════════════════════════
         LEFT — Brand panel (desktop)
    ══════════════════════════════ --}}
    <div class="brand-panel">
        <div class="brand-content">

            {{-- Logo --}}
            <div class="brand-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12h20M2 12c0 0 2-4 6-5s8 1 12 5"/>
                    <path d="M4 12v4a1 1 0 001 1h14a1 1 0 001-1v-4"/>
                    <path d="M8 12V9"/>
                    <circle cx="8" cy="17" r="1.5"/>
                    <circle cx="16" cy="17" r="1.5"/>
                </svg>
            </div>

            <div class="brand-text">
                <div class="brand-name">New Shoes Ltd</div>
                <div class="brand-tagline">
                    Your trusted partner in <strong>wholesale footwear</strong><br>
                    and everyday <strong>grocery essentials</strong>.
                </div>
            </div>

            <div class="brand-divider"></div>

            {{-- Features --}}
            <div class="brand-features">
                <div class="brand-feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="feature-title">Premium Footwear Selection</div>
                        <div class="feature-desc">Formal, casual, sport &amp; custom orders at wholesale prices.</div>
                    </div>
                </div>

                <div class="brand-feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                        </svg>
                    </div>
                    <div>
                        <div class="feature-title">Grocery &amp; FMCG Supplies</div>
                        <div class="feature-desc">Bulk grocery sourced directly from certified suppliers.</div>
                    </div>
                </div>

                <div class="brand-feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </div>
                    <div>
                        <div class="feature-title">Nationwide Distribution</div>
                        <div class="feature-desc">Fast delivery to retailers &amp; distribution points countrywide.</div>
                    </div>
                </div>

                <div class="brand-feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <div>
                        <div class="feature-title">Real-Time Inventory Tracking</div>
                        <div class="feature-desc">Live stock, session cash management &amp; full audit trails.</div>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="brand-stats">
                <div>
                    <div class="stat-value">500+</div>
                    <div class="stat-label">Retail Partners</div>
                </div>
                <div>
                    <div class="stat-value">12K+</div>
                    <div class="stat-label">SKUs Available</div>
                </div>
                <div>
                    <div class="stat-value">8 yrs</div>
                    <div class="stat-label">In Business</div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════
         RIGHT — Form panel
    ══════════════════════════════ --}}
    <div class="form-panel">
        <div class="form-inner">

            {{-- Form card --}}
            <div class="form-card">
                {{ $slot }}
            </div>

            <div class="form-footer">
                <strong>New Shoes Ltd</strong> &mdash; Wholesale Shoes &amp; Groceries<br>
                Powered by Smart Inventory &copy; {{ date('Y') }}
            </div>

        </div>
    </div>

</div>
@livewireScripts
</body>
</html>
