## System Manager Page — completed 2026-05-14

### Route & files
- `owner.system` → GET `/owner/system` → `resources/views/owner/system.blade.php`
- Sidebar link: "System" (server icon, no warning icon) in `resources/views/livewire/layout/sidebar.blade.php`
- Livewire component: `app/Livewire/Owner/SystemManager.php`
- Blade: `resources/views/livewire/owner/system-manager.blade.php`

### Two tabs
- **Setup** — inline CRUD for Product Categories, Expense Categories, Transporters
  (no page navigation, no tinker needed for initial data entry)
- **Wipe** — selective data deletion with 14 checkbox groups

### Setup tab — inline CRUD rules
- Product Categories: name (required), code (optional), description (optional); toggle active/inactive; forceDelete blocked if products assigned
- Expense Categories: name (required), applies_to (shop|warehouse|both), description; toggle blocked for 'Cash Shortage'; delete blocked if expenses recorded
- Transporters: name (required), phone, company, vehicle number; delete blocked if transfer records exist
- Each list uses inline confirm-row pattern (no modal) — `$catConfirmDelete`, `$expCatConfirmDelete`, `$trConfirmDelete`

### Wipe tab — deletion rules
- 14 groups with FK-safe deletion order hardcoded in `executeWipe()` map
- Confirm bar: label "TYPE DELETE TO CONFIRM" + standalone monospace input + "Delete Selected Data" button
- Alpine `x-model` + `@input="$wire.set(...)"` used for instant reactivity (wire:model alone only syncs on blur)
- Button is pink/dim (var(--red-dim)) when inactive; solid red + white text when DELETE typed and groups selected
- Two-step: `requestWipe()` validates → sets `$showConfirm` → modal → `executeWipe()`
- `executeWipe()` runs inside `DB::transaction()`; users table: deletes all except current owner id
- FK-safe order: reports → logs → sessions → sales → returns → transfers → credit → boxes → customers → users → transporters → products → categories → locations

### Bug fixes in this session
- `/shop/credit-repayments` — fixed `Undefined variable $reason` by switching `@include` to `<x-session-gate-blocked :reason="..." />` (proper Blade component prop passing)
- Operations Centre widget — credit repayments were counted in "Gross Sales"; split `$in` into `$salesIn` + `$repaymentIn`; widget now shows "SALES" with "+X repaid" subtitle
- Session close wizard — bank/card repayments were not reflected in BANK balance; `DailySessionService::computeLiveSummary()` now adds `$bankRepayments` (bank_transfer + card credit repayments) to `bank_available` and returns `total_repayments_bank`
