<div>
    @if (session()->has('success'))
        <div style="margin-bottom:10px;padding:9px 12px;border-radius:8px;font-size:12px;
                    background:var(--green-dim);color:var(--green);border:1px solid var(--green);">{{ session('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div style="margin-bottom:10px;padding:9px 12px;border-radius:8px;font-size:12px;
                    background:var(--red-dim);color:var(--red);border:1px solid var(--red);">{{ session('error') }}</div>
    @endif

    @if ($requests->isEmpty())
        <div style="text-align:center;padding:28px 0;font-size:12px;color:var(--text-faint);
                    border-radius:12px;background:var(--surface-raised);border:1px solid var(--border);">
            No pending warehouse requests
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:10px;">
            @foreach ($requests as $request)
                <div style="border-radius:14px;overflow:hidden;border:1px solid var(--border);">

                    {{-- Card header --}}
                    <div style="padding:12px 16px;background:var(--surface-raised);border-bottom:1px solid var(--border);
                                display:flex;align-items:center;justify-content:space-between;gap:10px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="font-size:11px;font-family:var(--font-mono);font-weight:700;color:var(--accent);">
                                {{ $request->reference_number }}
                            </span>
                            <span style="padding:2px 8px;border-radius:5px;font-size:10px;font-weight:700;
                                         background:var(--amber-dim);color:var(--amber);">
                                Pending
                            </span>
                        </div>
                        <span style="font-size:14px;font-weight:800;font-family:var(--font-mono);color:var(--text);">
                            {{ number_format($request->amount) }}
                            <span style="font-size:11px;font-weight:600;color:var(--text-dim);">RWF</span>
                        </span>
                    </div>

                    {{-- Card body --}}
                    <div style="background:var(--surface);padding:12px 16px;">
                        <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:4px;">
                            {{ $request->reason }}
                        </div>
                        <div style="font-size:11px;color:var(--text-dim);display:flex;gap:12px;flex-wrap:wrap;">
                            <span>From {{ $request->warehouse->name ?? 'Warehouse' }}</span>
                            <span>By {{ $request->requestedBy->name ?? '—' }}</span>
                            <span>{{ $request->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    {{-- Actions / rejection form --}}
                    @if ($rejectingId === $request->id)
                        <div style="padding:12px 16px;background:var(--surface-raised);border-top:1px solid var(--border);">
                            <label style="display:block;font-size:11px;font-weight:600;color:var(--text-dim);margin-bottom:5px;">
                                Rejection Reason
                            </label>
                            <input type="text" wire:model="rejectionReason"
                                   style="width:100%;padding:8px 12px;border-radius:8px;font-size:13px;margin-bottom:8px;
                                          background:var(--surface);border:1px solid var(--border);color:var(--text);box-sizing:border-box;"
                                   placeholder="Reason for rejection…">
                            @error('rejectionReason')
                                <div style="font-size:11px;margin-bottom:8px;color:var(--red);">{{ $message }}</div>
                            @enderror
                            <div style="display:flex;gap:6px;">
                                <button wire:click="submitRejection"
                                        style="padding:7px 16px;border-radius:8px;font-size:12px;font-weight:700;
                                               background:var(--red);color:white;border:none;cursor:pointer;">
                                    Confirm Reject
                                </button>
                                <button wire:click="cancelReject"
                                        style="padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;
                                               background:var(--surface);color:var(--text-dim);border:1px solid var(--border);cursor:pointer;">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    @else
                        <div style="padding:10px 16px;background:var(--surface-raised);border-top:1px solid var(--border);
                                    display:flex;gap:8px;">
                            <button wire:click="payRequest({{ $request->id }})"
                                    wire:confirm="Pay {{ number_format($request->amount) }} RWF from today's session cash?"
                                    style="padding:7px 16px;border-radius:8px;font-size:12px;font-weight:700;
                                           background:var(--green);color:white;border:none;cursor:pointer;
                                           display:flex;align-items:center;gap:5px;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Pay from Cash
                            </button>
                            <button wire:click="showRejectForm({{ $request->id }})"
                                    style="padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;
                                           background:var(--red-dim);color:var(--red);border:1px solid var(--red-dim);cursor:pointer;">
                                Reject
                            </button>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @endif
</div>
