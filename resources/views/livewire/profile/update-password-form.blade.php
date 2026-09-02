<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<div style="font-family:var(--font)">
<style>
.pwd-card       { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card);
                  height:100%;display:flex;flex-direction:column }
.pwd-card-head  { padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px }
.pwd-icon       { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.pwd-title      { font-size:15px;font-weight:800;color:var(--text);margin:0 }
.pwd-sub        { font-size:12px;color:var(--text-dim);margin-top:2px }
.pwd-card-body  { padding:22px;flex:1;display:flex;flex-direction:column }
.pwd-card-body form { flex:1;display:flex;flex-direction:column }
.pwd-fields     { flex:1 }
.pwd-field      { margin-bottom:18px }
.pwd-field:last-of-type { margin-bottom:0 }
.pwd-label      { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px }
.pwd-input      { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;
                  font-size:14px;background:var(--surface);color:var(--text);outline:none;
                  box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.pwd-input:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim) }
.pwd-error      { font-size:11px;color:var(--red);margin-top:6px }
.pwd-actions    { display:flex;align-items:center;gap:14px;margin-top:22px }
.pwd-btn-primary { padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;
                  font-family:var(--font);transition:opacity var(--tr);display:inline-flex;align-items:center;
                  gap:8px;background:var(--accent);color:#fff;border:none;box-shadow:0 3px 10px rgba(59,111,212,.25) }
.pwd-btn-primary:hover { opacity:.88 }
.pwd-btn-primary:disabled { opacity:.6;cursor:not-allowed }
.pwd-saved      { display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;
                  color:var(--green);background:var(--green-dim);padding:5px 10px;border-radius:20px }
@keyframes pwd-spin { to { transform:rotate(360deg) } }
</style>

<div class="pwd-card">
    <div class="pwd-card-head">
        <div class="pwd-icon" style="background:var(--accent-dim);color:var(--accent)">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <div>
            <h2 class="pwd-title">{{ __('Update Password') }}</h2>
            <p class="pwd-sub">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>
    </div>

    <div class="pwd-card-body">
        <form wire:submit="updatePassword">
            <div class="pwd-fields">
                <div class="pwd-field">
                    <label for="update_password_current_password" class="pwd-label">{{ __('Current Password') }}</label>
                    <input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" class="pwd-input" autocomplete="current-password">
                    @error('current_password') <div class="pwd-error">{{ $message }}</div> @enderror
                </div>

                <div class="pwd-field">
                    <label for="update_password_password" class="pwd-label">{{ __('New Password') }}</label>
                    <input wire:model="password" id="update_password_password" name="password" type="password" class="pwd-input" autocomplete="new-password">
                    @error('password') <div class="pwd-error">{{ $message }}</div> @enderror
                </div>

                <div class="pwd-field">
                    <label for="update_password_password_confirmation" class="pwd-label">{{ __('Confirm Password') }}</label>
                    <input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" class="pwd-input" autocomplete="new-password">
                    @error('password_confirmation') <div class="pwd-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="pwd-actions">
                <button type="submit" class="pwd-btn-primary" wire:loading.attr="disabled" wire:target="updatePassword">
                    <span wire:loading.remove wire:target="updatePassword">{{ __('Save') }}</span>
                    <span wire:loading wire:target="updatePassword" style="display:none;align-items:center;gap:8px">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                             style="animation:pwd-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                        {{ __('Saving…') }}
                    </span>
                </button>

                <x-action-message class="pwd-saved" on="password-updated">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </div>
</div>
</div>
