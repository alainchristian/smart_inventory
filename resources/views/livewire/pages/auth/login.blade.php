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

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Session status --}}
    @if (session('status'))
        <div class="auth-status">{{ session('status') }}</div>
    @endif

    <div class="form-eyebrow">Inventory System</div>
    <h1 class="form-heading">Welcome back</h1>
    <p class="form-subheading">Sign in to your account to continue</p>

    <form wire:submit="login" novalidate>

        {{-- Email --}}
        <div class="field-group">
            <label for="email" class="field-label">Email address</label>
            <input wire:model="form.email"
                   id="email"
                   type="email"
                   name="email"
                   class="field-input @error('form.email') has-error @enderror"
                   placeholder="you@example.com"
                   required
                   autofocus
                   autocomplete="username">
            @error('form.email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="field-group">
            <label for="password" class="field-label">Password</label>
            <div class="field-input-wrap" x-data="{ showPwd: false }">
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

    </form>

    <div class="form-footer">
        Powered by Smart Inventory &copy; {{ date('Y') }}
    </div>
</div>
