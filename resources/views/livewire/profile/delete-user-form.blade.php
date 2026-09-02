<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div style="font-family:var(--font)">
<style>
.del-card       { background:var(--surface);border:none;border-radius:var(--r);box-shadow:var(--shadow-card) }
.del-card-head  { padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px }
.del-icon       { width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.del-title      { font-size:15px;font-weight:800;color:var(--text);margin:0 }
.del-sub        { font-size:12px;color:var(--text-dim);margin-top:2px }
.del-card-body  { padding:22px }
.del-label      { display:block;font-size:12px;font-weight:700;color:var(--text-sub);margin-bottom:6px;letter-spacing:.3px }
.del-input      { width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;
                  font-size:14px;background:var(--surface);color:var(--text);outline:none;
                  box-sizing:border-box;font-family:var(--font);transition:border-color var(--tr) }
.del-input:focus { border-color:var(--red);box-shadow:0 0 0 3px var(--red-dim) }
.del-error      { font-size:11px;color:var(--red);margin-top:6px }
.del-btn-outline { padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;
                  font-family:var(--font);transition:all var(--tr);display:inline-flex;align-items:center;gap:6px;
                  background:var(--red-dim);border:1px solid var(--red);color:var(--red) }
.del-btn-outline:hover { background:var(--red);color:#fff }
.del-btn-red    { padding:11px 20px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;
                  font-family:var(--font);transition:opacity var(--tr);display:inline-flex;align-items:center;
                  justify-content:center;gap:8px;background:var(--red);color:#fff;border:none }
.del-btn-red:hover { opacity:.88 }
.del-btn-red:disabled { opacity:.6;cursor:not-allowed }
.del-btn-ghost  { padding:11px 20px;background:transparent;border:1.5px solid var(--border);color:var(--text-sub);
                  border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);
                  transition:all var(--tr) }
.del-btn-ghost:hover { border-color:var(--border-hi);color:var(--text) }
.del-modal-title { font-size:16px;font-weight:800;color:var(--text);margin:0 }
.del-modal-sub   { font-size:13px;color:var(--text-dim);margin-top:6px;line-height:1.5 }
.del-modal-foot  { margin-top:20px;display:flex;justify-content:flex-end;gap:10px }
@keyframes del-spin { to { transform:rotate(360deg) } }
</style>

<div class="del-card">
    <div class="del-card-head">
        <div class="del-icon" style="background:var(--red-dim);color:var(--red)">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <div>
            <h2 class="del-title">{{ __('Delete Account') }}</h2>
            <p class="del-sub">{{ __('Permanently delete your account and all associated data') }}</p>
        </div>
    </div>

    <div class="del-card-body">
        <p style="font-size:13px;color:var(--text-sub);line-height:1.6;margin:0 0 16px">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>

        <button
            type="button"
            class="del-btn-outline"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >{{ __('Delete Account') }}</button>
    </div>
</div>

<x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
    <form wire:submit="deleteUser" style="padding:22px;font-family:var(--font)">
        <h2 class="del-modal-title">{{ __('Are you sure you want to delete your account?') }}</h2>

        <p class="del-modal-sub">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
        </p>

        <div style="margin-top:18px">
            <label for="password" class="del-label">{{ __('Password') }}</label>
            <input
                wire:model="password"
                id="password"
                name="password"
                type="password"
                class="del-input"
                placeholder="{{ __('Password') }}"
            >
            @error('password') <div class="del-error">{{ $message }}</div> @enderror
        </div>

        <div class="del-modal-foot">
            <button type="button" class="del-btn-ghost" x-on:click="$dispatch('close')">
                {{ __('Cancel') }}
            </button>

            <button type="submit" class="del-btn-red" wire:loading.attr="disabled" wire:target="deleteUser">
                <span wire:loading.remove wire:target="deleteUser">{{ __('Delete Account') }}</span>
                <span wire:loading wire:target="deleteUser" style="display:none;align-items:center;gap:8px;justify-content:center">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                         style="animation:del-spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>
                    {{ __('Deleting…') }}
                </span>
            </button>
        </div>
    </form>
</x-modal>
</div>
