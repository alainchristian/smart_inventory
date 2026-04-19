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
                   class="field-input"
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
            <input wire:model="form.password"
                   id="password"
                   type="password"
                   name="password"
                   class="field-input"
                   placeholder="••••••••"
                   required
                   autocomplete="current-password">
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
            <span wire:loading wire:target="login" style="display:none">
                Signing in…
            </span>
        </button>

    </form>

    <div class="form-footer">
        <strong>New Shoes Ltd</strong> &mdash; Wholesale Shoes &amp; Groceries<br>
        Powered by Smart Inventory &copy; {{ date('Y') }}
    </div>
</div>
