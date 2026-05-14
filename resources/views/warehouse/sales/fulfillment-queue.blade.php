<x-app-layout>
  <div class="dashboard-page-header">
    <div>
      <h1>Fulfillment Queue</h1>
      <p>Pack and hand over boxes for warehouse direct sales</p>
    </div>
    <a href="{{ route('warehouse.dashboard') }}" wire:navigate
       style="padding:9px 16px;background:var(--surface2);color:var(--text-sub);
              border:1px solid var(--border);border-radius:var(--rx);font-size:13px;
              font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
      </svg>
      Dashboard
    </a>
  </div>

  <livewire:warehouse.sales.fulfillment-queue />
</x-app-layout>
