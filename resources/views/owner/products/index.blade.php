<x-app-layout>

  {{-- Page header --}}
  <div class="dashboard-page-header">
    <div>
      <h1>Products</h1>
      <p>Catalog management, stock health, pricing intelligence</p>
    </div>
    <livewire:dashboard.time-filter />
  </div>

  {{-- KPI row - always visible to owner --}}
  {{-- Card 4 (Best Margin) is self-gated inside product-kpi-row.blade.php --}}
  <div class="section-label">Catalog Overview</div>
  <livewire:owner.products.product-kpi-row />

  {{-- Product table --}}
  <div class="section-label">Product List</div>
  <livewire:products.product-list />

  {{-- Detail drawer - floats above, self-contained --}}
  <livewire:owner.products.product-detail />

</x-app-layout>
