<div style="display:flex;flex-direction:column;gap:20px;font-family:var(--font)">

    {{-- ── Sales Today ─────────────────────────────────────────── --}}
    <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:12px;overflow:hidden">
        <div style="padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-sub)">
                Sales Today
            </span>
            <input type="date" wire:model.live="selectedDate"
                style="padding:4px 10px;border-radius:7px;border:1px solid var(--border);
                       background:var(--surface2);color:var(--text);font-size:12px;cursor:pointer">
        </div>
        <div style="padding:22px;display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px">
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text-sub);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Revenue</div>
                <div style="font-size:26px;font-weight:800;color:var(--text);font-family:var(--mono)">
                    {{ number_format($salesToday['total']) }}
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:2px">RWF</div>
            </div>
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text-sub);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Transactions</div>
                <div style="font-size:26px;font-weight:800;color:var(--text);font-family:var(--mono)">
                    {{ number_format($salesToday['count']) }}
                </div>
                <div style="font-size:11px;color:var(--text-dim);margin-top:2px">sales</div>
            </div>
        </div>
    </div>

    {{-- ── Payment Breakdown ────────────────────────────────────── --}}
    <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:12px;overflow:hidden">
        <div style="padding:16px 22px;border-bottom:1px solid var(--border)">
            <span style="font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--text-sub)">
                Payment Breakdown
            </span>
        </div>
        <div style="padding:16px 22px;display:flex;flex-direction:column;gap:10px">
            @php
                $methods = [
                    'cash'          => ['label' => 'Cash',          'color' => 'var(--green)'],
                    'card'          => ['label' => 'Card',          'color' => 'var(--accent)'],
                    'mobile_money'  => ['label' => 'Mobile Money',  'color' => '#7c3aed'],
                    'bank_transfer' => ['label' => 'Bank Transfer', 'color' => '#0891b2'],
                    'credit'        => ['label' => 'Credit',        'color' => 'var(--red,#e11d48)'],
                ];
                $grandTotal = max(1, array_sum($paymentBreakdown));
            @endphp
            @foreach($methods as $key => $meta)
                @php $val = $paymentBreakdown[$key] ?? 0; @endphp
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:100px;font-size:12px;font-weight:600;color:var(--text-sub)">
                        {{ $meta['label'] }}
                    </div>
                    <div style="flex:1;height:6px;background:var(--surface3);border-radius:999px;overflow:hidden">
                        <div style="height:100%;border-radius:999px;background:{{ $meta['color'] }};
                                    width:{{ $val > 0 ? max(2, round($val / $grandTotal * 100)) : 0 }}%;
                                    transition:width .4s ease"></div>
                    </div>
                    <div style="width:110px;text-align:right;font-size:12px;font-weight:700;
                                font-family:var(--mono);color:var(--text)">
                        {{ number_format($val) }} RWF
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Credit Outstanding Widget ──────────────────────── --}}
        @if($creditOutstanding['customer_count'] > 0)
        <div style="margin:0 16px 16px;padding:12px 16px;background:rgba(225,29,72,.06);
                    border:1.5px solid rgba(225,29,72,.25);border-radius:10px;
                    display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:16px">⚠️</span>
                <div>
                    <div style="font-size:12px;font-weight:700;color:var(--red,#e11d48)">Credit Outstanding</div>
                    <div style="font-size:11px;color:var(--text-sub);margin-top:1px">
                        {{ $creditOutstanding['customer_count'] }} customer{{ $creditOutstanding['customer_count'] === 1 ? '' : 's' }}
                    </div>
                </div>
            </div>
            <div style="font-size:15px;font-weight:800;font-family:var(--mono);color:var(--red,#e11d48)">
                {{ number_format($creditOutstanding['total_outstanding']) }}
                <span style="font-size:11px;font-weight:600"> RWF</span>
            </div>
        </div>
        @endif
    </div>

</div>
