<div>
    @if($done)
        <div style="max-width:560px;margin:48px auto;">
            <div style="background:var(--surface);border:1.5px solid var(--green);border-radius:14px;padding:40px 36px;text-align:center;">
                <div style="width:56px;height:56px;border-radius:50%;background:var(--green-dim);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:var(--green);">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 style="font-size:20px;font-weight:700;color:var(--text);margin-bottom:10px;">System wiped successfully</h2>
                <p style="font-size:14px;color:var(--text-dim);line-height:1.6;margin-bottom:28px;">All data has been cleared. You are the only remaining user.</p>
                <a href="{{ route('owner.dashboard') }}"
                   style="display:inline-block;padding:10px 28px;border-radius:8px;background:var(--green);color:#fff;font-size:14px;font-weight:600;text-decoration:none;">
                    Go to Dashboard
                </a>
            </div>
        </div>
    @else
        <div style="max-width:600px;margin:48px auto;">
            <div style="background:var(--surface);border:1.5px solid var(--red);border-radius:14px;padding:36px 36px 32px;">
                <div class="flex items-start gap-4" style="margin-bottom:24px;">
                    <div style="width:48px;height:48px;border-radius:50%;background:var(--red-dim);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--red);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 style="font-size:18px;font-weight:700;color:var(--red);margin-bottom:6px;">Wipe All Data</h2>
                        <p style="font-size:13.5px;color:var(--text-dim);line-height:1.65;">
                            This will permanently delete <strong style="color:var(--text);">all products, categories, customers, sales, transfers, boxes, daily sessions, reports, expenses, alerts, and all users except your account</strong> from the database. Warehouses and shops will also be removed.
                        </p>
                        <p style="font-size:13px;color:var(--text-dim);margin-top:10px;line-height:1.6;">
                            What will be kept: your owner account, system settings, and expense categories.
                        </p>
                    </div>
                </div>

                <div style="border-top:1px solid var(--border);padding-top:24px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px;">
                        Type <span style="font-family:monospace;background:var(--surface2);padding:1px 6px;border-radius:4px;color:var(--red);">DELETE EVERYTHING</span> to confirm
                    </label>
                    <input
                        type="text"
                        wire:model="confirmText"
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="DELETE EVERYTHING"
                        style="width:100%;padding:10px 14px;border-radius:8px;border:1.5px solid var(--border);background:var(--surface2);color:var(--text);font-size:14px;font-family:monospace;outline:none;box-sizing:border-box;"
                    />
                    @error('confirmText')
                        <p style="margin-top:6px;font-size:12.5px;color:var(--red);">{{ $message }}</p>
                    @enderror

                    <div style="margin-top:20px;text-align:right;" x-data="{ confirmText: @entangle('confirmText') }">
                        <button
                            wire:click="requestWipe"
                            :disabled="confirmText !== 'DELETE EVERYTHING'"
                            :style="confirmText !== 'DELETE EVERYTHING' ? 'opacity:0.45;cursor:not-allowed;' : 'cursor:pointer;'"
                            style="padding:10px 24px;border-radius:8px;background:var(--red);color:#fff;font-size:14px;font-weight:600;border:none;transition:opacity 0.15s;display:inline-flex;align-items:center;gap:8px;"
                        >
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Wipe Everything
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if($showConfirm)
            <div style="position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9998;display:flex;align-items:center;justify-content:center;padding:24px;">
                <div style="background:var(--surface);border:1.5px solid var(--red);border-radius:16px;padding:40px 36px;max-width:440px;width:100%;text-align:center;">
                    <div style="width:56px;height:56px;border-radius:50%;background:var(--red-dim);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                        <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--red);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <h3 style="font-size:19px;font-weight:700;color:var(--text);margin-bottom:12px;">This cannot be undone</h3>
                    <p style="font-size:13.5px;color:var(--text-dim);line-height:1.65;margin-bottom:28px;">
                        All data will be permanently deleted from the database. You will remain as the only user. This action is irreversible.
                    </p>
                    <div class="flex items-center justify-center gap-3">
                        <button
                            wire:click="cancelWipe"
                            style="padding:10px 22px;border-radius:8px;background:var(--surface2);color:var(--text);font-size:14px;font-weight:600;border:1px solid var(--border);cursor:pointer;"
                        >
                            Cancel
                        </button>
                        <button
                            wire:click="executeWipe"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60"
                            style="padding:10px 22px;border-radius:8px;background:var(--red);color:#fff;font-size:14px;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:7px;"
                        >
                            <span wire:loading.remove wire:target="executeWipe" style="display:inline-flex;align-items:center;gap:7px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Yes, delete everything
                            </span>
                            <span wire:loading wire:target="executeWipe" style="display:none;align-items:center;gap:7px;">
                                Deleting…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
