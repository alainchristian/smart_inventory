<x-app-layout>

  {{-- Page header --}}
  <div class="dashboard-page-header">
    <div>
      <h1>Products</h1>
      <p>Catalog management, stock health, pricing intelligence</p>
    </div>
    <livewire:dashboard.time-filter />
  </div>

  {{-- KPI row - always visible to owner, card 4 self-gates behind viewPurchasePrice --}}
  <div class="section-label">Catalog Overview</div>
  <livewire:owner.products.product-kpi-row />

  {{-- Product table --}}
  <div class="section-label" style="margin-top:22px">Product List</div>
  <livewire:products.product-list />

  {{-- Detail drawer - floats above, self-contained --}}
  <livewire:owner.products.product-detail />

</x-app-layout>