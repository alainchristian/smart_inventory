# Credit Repayment — Complete Side Effects Fix
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read CREDIT_REPAYMENT_FIX.md and follow every step in order."

---

## Read these files first before writing any code

```bash
cat app/Livewire/Shop/CreditRepayments.php
cat app/Livewire/ShopManager/Dashboard.php
cat app/Models/Customer.php
cat app/Models/Alert.php
cat app/Models/ActivityLog.php
```

---

## What is broken

### Problem 1 — No activity log after repayment
`CreditRepayments::recordRepayment()` creates the DB record and updates
the customer balance but writes nothing to `activity_logs`. Every financial
action must leave a trace.

### Problem 2 — No alert resolution after full repayment
When a customer's `outstanding_balance` reaches 0 after a repayment, any
open credit alerts for that customer are never resolved automatically.

### Problem 3 — Shop manager dashboard payment breakdown is stale
`ShopManager/Dashboard.php::getPaymentMethodBreakdown()` reads from
`sales.payment_method` column. Since split payments were introduced, the
real source of truth is the `sale_payments` table. A cash+credit split
sale shows only "cash" in the dashboard because `payment_method` is set
to the primary method. The credit portion is invisible.

### Problem 4 — Analytics cache not cleared after repayment
The sales analytics and customer credit report pages cache their queries.
When a repayment is recorded, `customers.outstanding_balance` changes but
the cache still serves the old value. The owner sees stale credit numbers.

---

## STEP 1 — Fix CreditRepayments::recordRepayment()

**File:** `app/Livewire/Shop/CreditRepayments.php`

Find the `DB::transaction(function () use ($customer, $amount) {` block
inside `recordRepayment()`. It currently does two things: creates the
repayment record and updates the customer. Replace the entire transaction
closure with this:

```php
DB::transaction(function () use ($customer, $amount) {
    // 1. Create repayment record
    CreditRepayment::create([
        'customer_id'    => $customer->id,
        'shop_id'        => auth()->user()->location_id,
        'amount'         => $amount,
        'payment_method' => $this->paymentMethod,
        'reference'      => $this->reference ?: null,
        'notes'          => $this->notes ?: null,
        'recorded_by'    => auth()->id(),
        'repayment_date' => now(),
    ]);

    // 2. Update customer balances
    $newBalance = max(0, $customer->outstanding_balance - $amount);
    $customer->update([
        'total_repaid'        => $customer->total_repaid + $amount,
        'outstanding_balance' => $newBalance,
    ]);

    // 3. Write activity log
    \App\Models\ActivityLog::create([
        'user_id'           => auth()->id(),
        'user_name'         => auth()->user()?->name,
        'action'            => 'credit_repayment_recorded',
        'entity_type'       => 'Customer',
        'entity_id'         => $customer->id,
        'entity_identifier' => $customer->name . ' (' . $customer->phone . ')',
        'details'           => [
            'amount'             => $amount,
            'payment_method'     => $this->paymentMethod,
            'reference'          => $this->reference ?: null,
            'new_balance'        => $newBalance,
            'previous_balance'   => $customer->outstanding_balance,
            'fully_paid'         => $newBalance === 0,
            'shop_id'            => auth()->user()->location_id,
        ],
        'ip_address'        => request()->ip(),
        'user_agent'        => request()->header('User-Agent'),
    ]);

    // 4. Resolve open credit alerts for this customer if fully paid
    if ($newBalance === 0) {
        \App\Models\Alert::where('entity_type', 'Customer')
            ->where('entity_id', $customer->id)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at'      => now(),
                'resolution_notes' => 'Outstanding balance cleared by repayment on ' . now()->format('d M Y'),
            ]);
    }

    // 5. Bust analytics cache so dashboards show fresh numbers
    // Works for both file and Redis cache drivers
    try {
        $patterns = [
            "analytics_credit_*",
            "analytics_payment_methods_*",
            "analytics_kpi_*",
        ];
        // Tag-based flush (Redis/Memcached)
        if (method_exists(\Illuminate\Support\Facades\Cache::getStore(), 'tags')) {
            \Illuminate\Support\Facades\Cache::tags(['analytics'])->flush();
        }
        // Key-based flush for common cache keys
        foreach ([
            'shop_dashboard_payment_breakdown_' . auth()->user()->location_id,
            'shop_dashboard_payment_breakdown_' . auth()->user()->location_id . '_' . now()->toDateString(),
        ] as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }
    } catch (\Exception $e) {
        // Cache flush failure must never break the repayment transaction
        \Illuminate\Support\Facades\Log::warning('Cache flush failed after repayment: ' . $e->getMessage());
    }
});
```

Also add this import at the top of the file if not already present:

```php
use App\Models\CreditRepayment;
```

---

## STEP 2 — Fix ShopManager Dashboard payment breakdown

**File:** `app/Livewire/ShopManager/Dashboard.php`

Find the `getPaymentMethodBreakdown()` method. It currently reads from
`sales.payment_method`. Replace the entire method with this version that
reads from `sale_payments` instead:

```php
private function getPaymentMethodBreakdown(): array
{
    $date = $this->selectedDate ? \Carbon\Carbon::parse($this->selectedDate) : today();

    // Read from sale_payments table (the correct source for split payments)
    $rows = \Illuminate\Support\Facades\DB::table('sale_payments')
        ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
        ->whereNull('sales.voided_at')
        ->whereNull('sales.deleted_at')
        ->where('sales.shop_id', $this->shopId)
        ->whereDate('sales.sale_date', $date)
        ->selectRaw('sale_payments.payment_method::text as method, SUM(sale_payments.amount) as total')
        ->groupBy('sale_payments.payment_method')
        ->get()
        ->keyBy('method');

    return [
        'cash'          => (int) ($rows['cash']?->total          ?? 0),
        'card'          => (int) ($rows['card']?->total          ?? 0),
        'mobile_money'  => (int) ($rows['mobile_money']?->total  ?? 0),
        'bank_transfer' => (int) ($rows['bank_transfer']?->total ?? 0),
        'credit'        => (int) ($rows['credit']?->total        ?? 0),
    ];
}
```

---

## STEP 3 — Add credit outstanding widget to shop manager dashboard

**File:** `app/Livewire/ShopManager/Dashboard.php`

Add this new private method to the class:

```php
private function getShopCreditOutstanding(): array
{
    $outstanding = \App\Models\Customer::where('shop_id', $this->shopId)
        ->where('outstanding_balance', '>', 0)
        ->selectRaw('COUNT(*) as customer_count, SUM(outstanding_balance) as total_outstanding')
        ->first();

    return [
        'customer_count'    => (int) ($outstanding->customer_count   ?? 0),
        'total_outstanding' => (int) ($outstanding->total_outstanding ?? 0),
    ];
}
```

Then find the `render()` method and add `creditOutstanding` to the view data:

```php
public function render()
{
    return view('livewire.shop-manager.dashboard', [
        'salesToday'        => $this->getSalesToday(),
        'hourlySalesData'   => $this->getHourlySalesData(),
        'paymentBreakdown'  => $this->getPaymentMethodBreakdown(),
        'creditOutstanding' => $this->getShopCreditOutstanding(),
    ]);
}
```

---

## STEP 4 — Show credit outstanding in shop dashboard blade

**File:** Find the shop manager dashboard blade. Check the render() method
above to get the view name, then:

```bash
cat resources/views/livewire/shop-manager/dashboard.blade.php
# or
cat resources/views/livewire/shop/dashboard.blade.php
```

Find the section that displays the payment breakdown (cash/card/mobile money
rows). Immediately AFTER that section, add this credit outstanding widget:

```blade
@if($creditOutstanding['customer_count'] > 0)
<div style="margin-top:10px;padding:12px 16px;background:rgba(225,29,72,.06);
            border:1.5px solid rgba(225,29,72,.25);border-radius:10px;
            display:flex;align-items:center;justify-content:space-between">
    <div style="display:flex;align-items:center;gap:8px">
        <span style="font-size:16px">⚠️</span>
        <div>
            <div style="font-size:12px;font-weight:700;color:var(--red)">Credit Outstanding</div>
            <div style="font-size:11px;color:var(--text-sub);margin-top:1px">
                {{ $creditOutstanding['customer_count'] }} customer{{ $creditOutstanding['customer_count'] === 1 ? '' : 's' }}
            </div>
        </div>
    </div>
    <div style="font-size:15px;font-weight:800;font-family:var(--mono);color:var(--red)">
        {{ number_format($creditOutstanding['total_outstanding']) }}
        <span style="font-size:11px;font-weight:600"> RWF</span>
    </div>
</div>
@endif
```

---

## STEP 5 — Verify Alert model has resolved_at and resolution_notes columns

Run:

```bash
grep -r "resolved_at\|resolution_notes" database/migrations/ | grep alerts
```

If no results, run:

```bash
php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasColumn('alerts','resolved_at') ? 'yes' : 'no';"
```

If the column does not exist, create a migration:

```bash
php artisan make:migration add_resolution_fields_to_alerts_table
```

Then edit the generated migration file:

```php
public function up(): void
{
    Schema::table('alerts', function (Blueprint $table) {
        if (!Schema::hasColumn('alerts', 'resolved_at')) {
            $table->timestamp('resolved_at')->nullable()->after('is_resolved');
        }
        if (!Schema::hasColumn('alerts', 'resolution_notes')) {
            $table->text('resolution_notes')->nullable()->after('resolved_at');
        }
    });
}

public function down(): void
{
    Schema::table('alerts', function (Blueprint $table) {
        $table->dropColumn(['resolved_at', 'resolution_notes']);
    });
}
```

Then run:

```bash
php artisan migrate
```

If the columns already exist, skip this step entirely.

---

## STEP 6 — Fix CustomerCreditReport to not divide by 100

**File:** `resources/views/livewire/owner/reports/customer-credit-report.blade.php`

Search the file for any occurrence of `/ 100` in the credit KPI cards section:

```bash
grep -n "/ 100" resources/views/livewire/owner/reports/customer-credit-report.blade.php
```

The `customers` table stores amounts in RWF directly (not cents). Any
`/ 100` in this blade is wrong. Remove all occurrences of `/ 100` from
`$summary['total_credit_given']`, `$summary['total_repaid']`, and
`$summary['total_outstanding']` display values.

For example, change:
```blade
{{ number_format($summary['total_credit_given'] / 100, 0) }} RWF
```
to:
```blade
{{ number_format($summary['total_credit_given'], 0) }} RWF
```

Apply the same fix to every credit value in that blade that uses `/ 100`.

---

## STEP 7 — Clear all caches

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

---

## Do NOT touch

- `SaleService.php` — credit extension on sale creation is already correct
- `CustomerService.php` — `recordSalePurchase()` is already correct
- `CreditService.php` — this service is no longer the active path (shop uses `CreditRepayments.php` directly)
- Any migration files except the alert columns migration in Step 5
- The `sale_payments` table or its model

---

## Verification

1. Open the shop credit repayments page, record a repayment
2. Check `activity_logs` table — a `credit_repayment_recorded` row should exist
3. Record a repayment that clears the balance to 0
   - Check `customers.outstanding_balance` = 0
   - Check any open alerts for that customer now have `resolved_at` set
4. Open the shop manager dashboard → payment breakdown should now show
   the correct credit amount separately from cash
5. If customer has outstanding credit → red warning widget appears in dashboard
6. Open Owner → Reports → Customer Credit → KPI numbers should not be
   divided by 100 (they should match what the POS shows)
