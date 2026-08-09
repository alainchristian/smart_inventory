<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ config('tenant.name') }}</title>

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
            overflow: hidden;
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0f1e;
            -webkit-font-smoothing: antialiased;
        }

        /* ════════════════════════════════
           ROOT — two-column grid
           Fixed to the viewport so the page itself never scrolls;
           each side scrolls internally only if its own content
           can't fit (safety net for very short/narrow windows).
        ════════════════════════════════ */
        .login-root {
            height: 100vh;
            height: 100dvh;
            display: grid;
            grid-template-columns: 58% 42%;
            overflow: hidden;
        }

        /* ════════════════════════════════
           LEFT — Brand panel (photo bg)
        ════════════════════════════════ */
        .brand-panel {
            position: relative;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none;
        }
        .brand-panel::-webkit-scrollbar { display: none; }

        /*
         * Gradual left-to-right overlay:
         * — Left ~38%: opaque dark (text zone)
         * — 38–85%: long, slow dissolve so the photo is revealed gently
         * — Right 15%+: fully clear
         */
        .brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to right,
                rgba(8, 13, 32, 0.97) 0%,
                rgba(8, 13, 32, 0.96) 28%,
                rgba(8, 13, 32, 0.80) 42%,
                rgba(8, 13, 32, 0.50) 56%,
                rgba(8, 13, 32, 0.20) 72%,
                rgba(8, 13, 32, 0.04) 88%,
                rgba(8, 13, 32, 0.00) 100%
            );
            pointer-events: none;
            z-index: 1;
        }

        /* No ::after line — the long gradient is the separator */
        .brand-panel::after { display: none; }

        .brand-content {
            position: relative;
            z-index: 3;
            /* Narrower text zone — more photo visible */
            width: 46%;
            padding: 52px 0 52px 52px;
            animation: fadeInUp .55s .15s ease both;
        }

        /* Logo mark — icon + tenant name displayed inline, at every breakpoint */
        .brand-logo-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }
        .brand-logo {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #3b6fd4, #5b8fe8);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 28px rgba(59,111,212,.45), 0 0 0 1px rgba(255,255,255,.1);
        }
        .brand-logo-mono {
            font-family: 'DM Sans', sans-serif;
            font-weight: 800;
            font-size: 22px;
            line-height: 1;
            color: #fff;
        }

        .brand-name {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            line-height: 1.1;
            text-shadow: 0 2px 12px rgba(0,0,0,.4);
        }
        .brand-tagline {
            font-size: 14px;
            color: rgba(255,255,255,0.58);
            margin-bottom: 36px;
            line-height: 1.55;
        }
        .brand-tagline strong {
            color: #8ab4ef;
            font-weight: 600;
        }

        /* Divider */
        .brand-divider {
            width: 32px;
            height: 3px;
            background: linear-gradient(90deg, #3b6fd4, transparent);
            border-radius: 4px;
            margin-bottom: 30px;
        }

        /* Feature list — frosted pills */
        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 40px;
        }
        .brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.09);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .feature-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: rgba(59,111,212,.20);
            border: 1px solid rgba(59,111,212,.28);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .feature-icon svg {
            width: 15px;
            height: 15px;
            color: #8ab4ef;
        }
        .feature-title {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,.92);
        }
        .feature-desc {
            font-size: 11.5px;
            color: rgba(255,255,255,.42);
            line-height: 1.45;
        }

        /* Stats row — frosted glass card */
        .brand-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            border-radius: 14px;
            overflow: hidden;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .brand-stat {
            padding: 14px 16px;
            text-align: center;
            border-right: 1px solid rgba(255,255,255,0.07);
        }
        .brand-stat:last-child { border-right: none; }
        .stat-value {
            font-size: 20px;
            font-weight: 800;
            color: #3b6fd4;
            letter-spacing: -0.5px;
            line-height: 1;
        }
        .stat-label {
            font-size: 10.5px;
            color: rgba(255,255,255,.38);
            margin-top: 4px;
            letter-spacing: 0.2px;
        }

        /* ════════════════════════════════
           RIGHT — Form panel
        ════════════════════════════════ */
        .form-panel {
            background:
                linear-gradient(to right, rgba(59,111,212,.35), rgba(59,111,212,0) 6px),
                #f4f6fb;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 48px;
            /* Soft left-side shadow creates a gradual visual separation from the photo */
            box-shadow: -32px 0 64px rgba(8, 13, 32, 0.38);
            position: relative;
            z-index: 4;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none;
        }
        .form-panel::-webkit-scrollbar { display: none; }

        /* Soft ambient glows for warmth — purely decorative, sit behind the card.
           Clipped to their own layer (not .form-panel directly) so their
           intentionally-negative offsets never register as real scrollable
           content on .form-panel's overflow-y:auto. */
        .form-panel-glow {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }
        .form-panel-glow::before,
        .form-panel-glow::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
        }
        .form-panel-glow::before {
            width: 320px; height: 320px;
            top: -80px; right: -100px;
            background: radial-gradient(circle, rgba(59,111,212,.14), transparent 70%);
        }
        .form-panel-glow::after {
            width: 280px; height: 280px;
            bottom: -100px; left: -90px;
            background: radial-gradient(circle, rgba(139,180,239,.16), transparent 70%);
        }

        .form-inner {
            width: 100%;
            max-width: 380px;
            position: relative;
            z-index: 1;
        }

        /* Form card — white base, blue used only as a sparing accent.
           Structure (avatar circle + lines, icon-box pill-ish inputs,
           divider + footer) follows the reference; radii kept modest
           (>=10px) rather than full pill/stadium shapes. */
        .form-card {
            position: relative;
            background: #ffffff;
            border: 1px solid #e2e6f3;
            border-radius: 22px;
            padding: 40px 32px 32px;
            color: #1a1f36;
            box-shadow: 0 2px 12px rgba(26,31,54,.07), 0 8px 32px rgba(26,31,54,.05);
            animation: fadeInUp .35s ease both;
        }

        .sr-only {
            position: absolute; width: 1px; height: 1px;
            padding: 0; margin: -1px; overflow: hidden;
            clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        /* Avatar header — circle + flanking lines, replaces the old text heading */
        .form-avatar-wrap {
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 14px;
        }
        .form-avatar-line {
            flex: 1; height: 1px;
            background: linear-gradient(to right, transparent, #e2e6f3, transparent);
        }
        .form-avatar-circle {
            width: 64px; height: 64px; flex-shrink: 0;
            border-radius: 50%;
            border: 1.5px solid rgba(28,36,64,.22);
            background: linear-gradient(135deg, rgba(28,36,64,.12), rgba(59,111,212,.06));
            display: flex; align-items: center; justify-content: center;
        }
        .form-avatar-circle svg { width: 26px; height: 26px; color: #1c2440; }
        .form-caption {
            text-align: center; font-size: 13px;
            color: #4a5372;
            margin-bottom: 26px;
        }

        /* Field */
        .field-group { margin-bottom: 16px; }
        .field-input-wrap {
            display: flex; align-items: stretch;
            border: 1.5px solid #e2e6f3;
            border-radius: 10px;
            background: #f4f6fb;
            overflow: hidden;
            transition: border-color .15s, background .15s, box-shadow .15s;
        }
        .field-input-wrap:focus-within {
            border-color: #3b6fd4;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59,111,212,.12);
        }
        .field-input-wrap:has(.field-input.has-error) {
            border-color: #e11d48;
            background: #fff;
        }
        .field-icon-box {
            width: 44px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-right: 1.5px solid #e2e6f3;
            color: #7a81a0;
        }
        .field-icon-box svg { width: 16px; height: 16px; }
        .field-input-wrap:focus-within .field-icon-box { color: #3b6fd4; }
        .field-input {
            flex: 1; min-width: 0;
            padding: 11px 16px;
            border: none;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: #1a1f36;
            background: transparent;
            outline: none;
        }
        .field-input::placeholder {
            color: #7a81a0;
            text-transform: uppercase;
            letter-spacing: .4px;
            font-size: 12.5px;
            font-weight: 600;
        }

        .field-error {
            font-size: 12px;
            color: #e11d48;
            margin-top: 6px;
            padding-left: 6px;
        }

        .field-input:-webkit-autofill,
        .field-input:-webkit-autofill:hover,
        .field-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #1a1f36;
            -webkit-box-shadow: 0 0 0 1000px #f4f6fb inset;
            transition: background-color 5000s ease-in-out 0s;
        }

        .field-eye {
            width: 44px; flex-shrink: 0;
            background: none; border: none; cursor: pointer;
            color: #7a81a0;
            display: flex; align-items: center; justify-content: center;
            transition: color .15s; line-height: 0;
        }
        .field-eye:hover { color: #1a1f36; }
        .field-eye svg { width: 16px; height: 16px; }

        .form-meta {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 22px; gap: 8px;
        }
        .remember-label {
            display: flex; align-items: center;
            gap: 8px; font-size: 13px;
            color: #4a5372; cursor: pointer; user-select: none;
        }
        .xx-checkbox { position: relative; width: 17px; height: 17px; flex-shrink: 0; display: inline-flex; }
        .xx-checkbox input {
            position: absolute; opacity: 0; width: 100%; height: 100%; margin: 0; cursor: pointer;
        }
        .xx-checkbox-box {
            position: absolute; inset: 0;
            border: 1.5px solid #e2e6f3; border-radius: 5px; background: #fff;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s, border-color .15s, box-shadow .15s;
        }
        .xx-checkbox-box svg {
            width: 11px; height: 11px; stroke: #fff; fill: none;
            opacity: 0; transform: scale(.6);
            transition: opacity .12s, transform .12s;
        }
        .xx-checkbox input:checked ~ .xx-checkbox-box {
            background: #3b6fd4;
            border-color: #3b6fd4;
        }
        .xx-checkbox input:checked ~ .xx-checkbox-box svg { opacity: 1; transform: scale(1); }
        .xx-checkbox input:focus-visible ~ .xx-checkbox-box {
            box-shadow: 0 0 0 3px rgba(59,111,212,.25);
        }
        .forgot-link {
            font-size: 13px; font-weight: 500;
            color: #3b6fd4; text-decoration: none; white-space: nowrap;
        }
        .forgot-link:hover { text-decoration: underline; }

        .btn-login {
            position: relative;
            width: 100%; padding: 10px;
            background: linear-gradient(160deg, #1c2440, #0f1626);
            color: #fff;
            border: none; border-radius: 10px;
            font-size: 13.5px; font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: 0.2px; cursor: pointer;
            box-shadow: 0 3px 10px rgba(10,15,30,.3);
            transition: background .15s ease, box-shadow .15s ease;
            display: flex; align-items: center; justify-content: center;
        }
        .btn-login:hover { background: linear-gradient(160deg, #262f52, #141b30); }
        .btn-login:active { background: #0f1626; }
        .btn-login:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(28,36,64,.28), 0 3px 10px rgba(10,15,30,.3);
        }
        .btn-login:disabled { opacity: .65; cursor: not-allowed; }

        .spinner {
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            flex-shrink: 0;
        }
        .btn-loading { display: inline-flex; align-items: center; gap: 8px; }

        .auth-status {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 13px; border-radius: 8px;
            font-size: 13px; margin-bottom: 18px;
            background: rgba(14,158,134,.07);
            color: #0e9e86; border: 1px solid rgba(14,158,134,.18);
        }
        .auth-status svg { width: 15px; height: 15px; flex-shrink: 0; }

        .form-divider {
            height: 1px;
            background: #e2e6f3;
            margin: 22px 0 16px;
        }
        .form-footer {
            text-align: center;
            font-size: 11.5px; color: #7a81a0; line-height: 1.6;
        }

        /* ════════════════════════════════
           KEYFRAMES
        ════════════════════════════════ */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ════════════════════════════════
           RESPONSIVE
        ════════════════════════════════ */
        @media (max-width: 1024px) {
            .login-root { grid-template-columns: 52% 48%; }
            .brand-content { padding: 40px 40px 44px; }
            .form-panel { padding: 48px 32px; }
        }

        @media (max-width: 1100px) {
            .brand-content { width: 54%; padding-left: 40px; }
        }

        /* Tablet / large phone: stack — brand becomes compact top banner */
        @media (max-width: 768px) {
            .login-root {
                grid-template-columns: 1fr;
                min-height: 100vh;
            }
            .brand-panel {
                justify-content: center;
                min-height: 160px;
                max-height: 200px;
            }
            /* Back to top-bottom gradient for the horizontal banner */
            .brand-panel::before {
                background: linear-gradient(
                    to bottom,
                    rgba(8,13,32,0.55) 0%,
                    rgba(8,13,32,0.88) 100%
                );
            }
            .brand-panel::after { display: none; }
            .brand-content {
                width: 100%;
                padding: 20px 24px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                animation: none;
            }
            .brand-logo-row { gap: 12px; margin-bottom: 2px; }
            .brand-logo {
                width: 44px; height: 44px;
                border-radius: 12px; flex-shrink: 0;
            }
            .brand-name    { font-size: 20px; }
            .brand-tagline { font-size: 12px; margin-bottom: 0; }
            .brand-divider, .brand-features, .brand-stats { display: none; }
            .form-panel {
                justify-content: flex-start; padding: 36px 24px 40px;
                background:
                    linear-gradient(to bottom, rgba(59,111,212,.35), rgba(59,111,212,0) 6px),
                    #f4f6fb;
            }
            .form-inner { max-width: 500px; }
            .form-card  { padding: 28px 24px; }
        }

        @media (max-width: 480px) {
            .brand-panel   { min-height: 130px; max-height: 160px; }
            .brand-content { padding: 16px 20px; }
            .brand-logo-row { gap: 10px; }
            .brand-logo    { width: 38px; height: 38px; border-radius: 10px; }
            .brand-logo-mono { font-size: 16px; }
            .brand-name    { font-size: 17px; }
            .brand-tagline { display: none; }
            .form-panel    { padding: 28px 16px 32px; }
            .form-card     { padding: 24px 20px; border-radius: 14px; }
            .form-avatar-circle { width: 56px; height: 56px; }
            .form-avatar-circle svg { width: 24px; height: 24px; }
            .field-input   { font-size: 16px; }
            .btn-login     { font-size: 15px; padding: 13px; }
        }

        @media (max-width: 360px) {
            .brand-content { padding: 13px 16px; }
            .form-panel    { padding: 24px 12px; }
            .form-card     { padding: 20px 16px; }
        }

        @media (min-width: 769px) and (min-height: 900px) {
            .brand-content { padding-top: 64px; padding-bottom: 64px; }
            .form-panel    { padding-top: 80px; padding-bottom: 80px; }
        }

        /* ════════════════════════════════
           SHORT VIEWPORTS — compress vertical
           rhythm so both panels fit without
           needing to scroll internally
        ════════════════════════════════ */
        @media (min-width: 769px) and (max-height: 820px) {
            .brand-content  { padding: 28px 0 28px 40px; }
            .brand-tagline  { margin-bottom: 22px; }
            .brand-features { gap: 8px; margin-bottom: 22px; }
            .brand-feature  { padding: 8px 12px; }
            .brand-stats    { display: none; }
            .form-panel     { padding: 28px 40px; }
            .form-card      { padding: 26px 26px; }
        }
        @media (min-width: 769px) and (max-height: 680px) {
            .brand-divider  { margin-bottom: 16px; }
            .brand-feature .feature-desc { display: none; }
            .brand-features { margin-bottom: 14px; }
            .form-avatar-circle { width: 52px; height: 52px; }
            .form-avatar-circle svg { width: 22px; height: 22px; }
            .field-group    { margin-bottom: 14px; }
            .form-caption   { margin-bottom: 18px; }
        }
        @media (min-width: 769px) and (max-height: 560px) {
            .brand-content   { padding: 18px 0 18px 40px; }
            .brand-features  { display: none; }
            .brand-divider   { margin-bottom: 14px; }
            .form-card       { padding: 20px 24px; }
            .form-divider, .form-footer { display: none; }
        }

        @media (max-width: 768px) and (max-height: 700px) {
            .brand-panel    { min-height: 110px; max-height: 130px; }
            .brand-content  { padding: 14px 20px; }
            .brand-logo     { width: 38px; height: 38px; border-radius: 10px; }
            .brand-tagline  { display: none; }
            .form-panel     { padding: 18px 20px 22px; justify-content: flex-start; }
            .form-card      { padding: 20px 18px; }
            .form-avatar-circle { width: 48px; height: 48px; }
            .form-avatar-circle svg { width: 20px; height: 20px; }
            .form-caption   { margin-bottom: 16px; }
            .field-group    { margin-bottom: 12px; }
        }
        @media (max-width: 768px) and (max-height: 520px) {
            .brand-panel    { min-height: 0; max-height: 0; padding: 0; border: none; }
            .brand-panel *  { display: none; }
        }
    </style>
</head>
<body>
<div class="login-root">

    {{-- ══════════════════════════════
         LEFT — Brand panel
    ══════════════════════════════ --}}
    <div class="brand-panel"
         style="background-image: url('{{ asset('images/ChatGPT Image Apr 23, 2026, 06_09_43 PM.png') }}')">

        <div class="brand-content">

            {{-- Logo mark + tenant name, always inline --}}
            <div class="brand-logo-row">
                <div class="brand-logo">
                    <span class="brand-logo-mono">{{ config('tenant.monogram') }}</span>
                </div>
                <div class="brand-name">{{ config('tenant.name') }}</div>
            </div>

            <div class="brand-tagline">
                Your trusted partner in <strong>wholesale footwear</strong><br>
                and everyday <strong>grocery essentials</strong>.
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

            {{-- Stats — frosted glass --}}
            <div class="brand-stats">
                <div class="brand-stat">
                    <div class="stat-value">500+</div>
                    <div class="stat-label">Retail Partners</div>
                </div>
                <div class="brand-stat">
                    <div class="stat-value">12K+</div>
                    <div class="stat-label">SKUs Available</div>
                </div>
                <div class="brand-stat">
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
        <div class="form-panel-glow"></div>

        <div class="form-inner">
            <div class="form-card">
                {{ $slot }}
            </div>
        </div>
    </div>

</div>
@livewireScripts
</body>
</html>
