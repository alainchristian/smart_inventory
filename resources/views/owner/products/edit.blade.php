<x-app-layout>

  {{-- Breadcrumb header --}}
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
        <span style="font-size:12px;color:var(--text-sub)">Edit</span>
      </div>
      <h1>Edit Product</h1>
      <p>{{ $product->name }} &middot; <span style="font-family:var(--mono);font-size:13px">{{ $product->sku }}</span></p>
    </div>

    {{-- Product status badge --}}
    <span style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;align-self:flex-start;
                 background:{{ $product->is_active ? 'var(--green-dim)' : 'var(--surface2)' }};
                 color:{{ $product->is_active ? 'var(--green)' : 'var(--text-dim)' }}">
      {{ $product->is_active ? 'Active' : 'Inactive' }}
    </span>
  </div>

  <livewire:products.edit-product :product="$product" />

</x-app-layout>
