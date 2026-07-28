<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.login')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();
        Session::forget('url.intended');

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Session status --}}
    @if (session('status'))
        <div class="auth-status">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <div class="form-eyebrow">Inventory System</div>
    <div class="form-heading-row">
        <div class="form-heading-wave">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 11V6a2 2 0 00-2-2v0a2 2 0 00-2 2v0"/>
                <path d="M14 10V4a2 2 0 00-2-2v0a2 2 0 00-2 2v2"/>
                <path d="M10 10.5V6a2 2 0 00-2-2v0a2 2 0 00-2 2v8"/>
                <path d="M18 8a2 2 0 114 0v6a8 8 0 01-8 8h-2c-2.8 0-4.5-.86-6.53-3.34l-4-5c-.55-.65-.42-1.62.28-2.14v0a1.6 1.6 0 012.13.2L6 13"/>
            </svg>
        </div>
        <h1 class="form-heading">Welcome back</h1>
    </div>
    <p class="form-subheading">Sign in to your account to continue</p>

    <form wire:submit="login" novalidate>

        {{-- Email --}}
        <div class="field-group">
            <label for="email" class="field-label">Email address</label>
            <div class="field-input-wrap">
                <span class="field-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                        <polyline points="22 6 12 13 2 6"/>
                    </svg>
                </span>
                <input wire:model="form.email"
                       id="email"
                       type="email"
                       name="email"
                       class="field-input @error('form.email') has-error @enderror"
                       placeholder="you@example.com"
                       required
                       autofocus
                       autocomplete="username">
            </div>
            @error('form.email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="field-group">
            <label for="password" class="field-label">Password</label>
            <div class="field-input-wrap" x-data="{ showPwd: false }">
                <span class="field-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </span>
                <input wire:model="form.password"
                       id="password"
                       :type="showPwd ? 'text' : 'password'"
                       name="password"
                       class="field-input @error('form.password') has-error @enderror"
                       placeholder="••••••••"
                       required
                       autocomplete="current-password">
                <button type="button" class="field-eye" @click="showPwd = !showPwd"
                        :aria-label="showPwd ? 'Hide password' : 'Show password'"
                        tabindex="-1">
                    {{-- Eye open (shown when password is hidden) --}}
                    <svg x-show="!showPwd" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    {{-- Eye off (shown when password is visible) --}}
                    <svg x-show="showPwd" style="display:none" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                        <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>
            @error('form.password')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember / forgot --}}
        <div class="form-meta">
            <label class="remember-label" for="remember">
                <input wire:model="form.remember" id="remember" type="checkbox" name="remember">
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a class="forgot-link" href="{{ route('password.request') }}" wire:navigate>
                    Forgot password?
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-login" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">
                Sign In
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="margin-left:4px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </span>
            <span wire:loading wire:target="login" class="btn-loading" style="display:none">
                <span class="spinner"></span>
                Signing in…
            </span>
        </button>

        <div class="trust-note">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="10" rx="2"/>
                <path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
            Your credentials are encrypted and never shared
        </div>

    </form>

    <div class="form-footer">
        Powered by Smart Inventory &copy; {{ date('Y') }}
    </div>
</div>
