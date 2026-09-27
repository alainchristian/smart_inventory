<x-app-layout>
  {{-- The component renders its own title header; this wrapper only adds the
       back link (a second "Warehouse Sale" title stacked above it on phones). --}}
  <div style="margin-bottom:14px">
    <a href="{{ route('shop.pos') }}" wire:navigate
       style="padding:7px 14px;background:var(--surface);color:var(--text-sub);
              border:1px solid var(--border);border-radius:var(--rsm);font-size:13px;
              font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
      </svg>
      POS
    </a>
  </div>

  <livewire:shop.sales.warehouse-sale />
</x-app-layout>
