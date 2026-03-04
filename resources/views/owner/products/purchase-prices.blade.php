<x-app-layout>

  <div class="dashboard-page-header">
    <div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <a href="{{ route('owner.products.index') }}"
           style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:600">
          Products
        </a>
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"
             viewBox="0 0 24 24" style="color:var(--text-dim)">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
        <span style="font-size:12px;color:var(--text-sub)">Product Prices</span>
      </div>
      <h1>Product Prices</h1>
      <p>Upload purchase and selling prices — purchase prices are private (not visible to warehouse or shop managers)</p>
    </div>
  </div>

  <livewire:owner.products.upload-purchase-prices />

</x-app-layout>
