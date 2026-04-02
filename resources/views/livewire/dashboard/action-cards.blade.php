{{--
    action-cards.blade.php
    Replaces the amber pending-actions banner in dashboard.blade.php.
    - Uses only CSS variables from the design system (no Tailwind color classes).
    - Auto-hides when all counts are zero (renders nothing).
    - wire:poll.30s keeps counts fresh.
--}}
<div wire:poll.30s="loadCounts">
@if($this->hasPendingActions)
<div class="action-cards-grid" style="margin-bottom:20px">

    {{-- Transfer Approvals --}}
    @if($pendingApprovalCount > 0)
    <a href="{{ route('owner.transfers.index', ['status' => 'pending']) }}"
       class="action-card action-card--amber"
       style="text-decoration:none">
        <div class="action-card__header">
            <div class="action-card__icon-wrap" style="background:var(--amber-dim)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="var(--amber)" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <polyline points="17 1 21 5 17 9"/>
                    <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                    <polyline points="7 23 3 19 7 15"/>
                    <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                </svg>
            </div>
            <span class="action-card__label">Transfer Approvals</span>
        </div>
        <div class="action-card__count" style="color:var(--amber)">{{ $pendingApprovalCount }}</div>
        <div class="action-card__desc">
            {{ $pendingApprovalCount === 1 ? 'transfer' : 'transfers' }} awaiting approval
        </div>
    </a>
    @endif

    {{-- Discrepancies --}}
    @if($discrepancyCount > 0)
    <a href="{{ route('owner.transfers.index', ['status' => 'discrepancy']) }}"
       class="action-card action-card--red"
       style="text-decoration:none">
        <div class="action-card__header">
            <div class="action-card__icon-wrap" style="background:var(--red-dim)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="var(--red)" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <span class="action-card__label">Discrepancies</span>
        </div>
        <div class="action-card__count" style="color:var(--red)">{{ $discrepancyCount }}</div>
        <div class="action-card__desc">
            unresolved {{ $discrepancyCount === 1 ? 'discrepancy' : 'discrepancies' }}
        </div>
    </a>
    @endif

    {{-- Damaged Goods --}}
    @if($damagedPendingCount > 0)
    <a href="#"
       class="action-card action-card--amber"
       style="text-decoration:none">
        <div class="action-card__header">
            <div class="action-card__icon-wrap" style="background:var(--amber-dim)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="var(--amber)" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13" rx="2"/>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
            </div>
            <span class="action-card__label">Damaged Goods</span>
        </div>
        <div class="action-card__count" style="color:var(--amber)">{{ $damagedPendingCount }}</div>
        <div class="action-card__desc">pending disposition decision</div>
    </a>
    @endif

    {{-- Critical Alerts --}}
    @if($criticalAlertsCount > 0)
    <a href="#"
       class="action-card action-card--red"
       style="text-decoration:none">
        <div class="action-card__header">
            <div class="action-card__icon-wrap" style="background:var(--red-dim)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="var(--red)" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <span class="action-card__label">Critical Alerts</span>
        </div>
        <div class="action-card__count" style="color:var(--red)">{{ $criticalAlertsCount }}</div>
        <div class="action-card__desc">
            {{ $criticalAlertsCount === 1 ? 'alert' : 'alerts' }} require attention
        </div>
    </a>
    @endif

    {{-- Price Override Approvals --}}
    @if($pendingHeldCount > 0)
    <div class="action-card action-card--amber" style="cursor:default">
        <div class="action-card__header">
            <div class="action-card__icon-wrap" style="background:var(--amber-dim)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="var(--amber)" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
            </div>
            <span class="action-card__label">Price Approvals</span>
        </div>
        <div class="action-card__count" style="color:var(--amber)">{{ $pendingHeldCount }}</div>
        <div class="action-card__desc">
            held {{ $pendingHeldCount === 1 ? 'sale' : 'sales' }} need price approval — see below
        </div>
    </div>
    @endif

</div>
@endif
</div>
