## Sales History Page — completed 2026-05-02

### Files
- `app/Livewire/Shop/Sales/SalesIndex.php` — component
- `resources/views/livewire/shop/sales/sales-index.blade.php` — Livewire template
- `resources/views/shop/sales/index.blade.php` — wrapper (CSS, `sli-` prefix)

### Route
- `shop.sales.index` → GET `/shop/sales`

### Features
- **KPI cards** (5): Total Revenue, Transactions, Avg. Transaction, Cash Collected, Credit Issued — all react to active date + payment filter
- **Filters**: Period (segmented pills, horizontal scroll) + Payment method (segmented pills, horizontal scroll on ≤660px)
- **Search**: sale number, customer name, customer phone (`ilike`)
- **Infinite scroll**: `$perPage` increments by 20 via `loadMore()`; Alpine `IntersectionObserver` sentinel with `rootMargin: '300px'`; no pagination component
- **Expandable rows**: items sold, payment breakdown, sale metadata, print receipt link
- **Sortable columns**: sale_number, sale_date, total

### Key rules
- `$sales` is a **Collection** (not LengthAwarePaginator) — use `$totalFiltered` for count, never `$sales->total()`
- Payment filter `credit` must use `where('has_credit', true)` — NOT `where('payment_method', 'credit')`
- `$summaryCash` query uses `sales.has_credit = true` condition when filter is `credit` (not `sales.payment_method`)
- `applyPaymentFilter()` is called on BOTH the main query and the summary base query so KPI cards always match table results
- Filter resets `$perPage = 20` in `updatingSearch`, `updatingDateFilter`, `updatingPaymentFilter`, `sort()`
