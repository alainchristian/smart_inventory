# Owner Settings Module
## Claude Code Instructions

> Drop in project root and tell Claude Code:
> "Read OWNER_SETTINGS.md and follow every step in order."

---

## Read these files first

```bash
cat routes/web.php | grep -A5 "owner.settings"
cat resources/views/components/sidebar.blade.php | grep -A3 "settings"
cat app/Models/Category.php
```

---

## What we are building

A `settings` table and `SettingsService` that the owner configures from
a dedicated page at `/owner/settings`. Other modules (POS, returns,
credit) will read these settings instead of using hardcoded values.

Settings are grouped into four sections:
1. **Sales Rules** — individual item sales control per category
2. **Returns Policy** — who can process, approval threshold, days limit
3. **Credit Policy** — on/off, require customer, max per customer
4. **Price Override** — threshold % before owner approval is required

---

## STEP 1 — Migration: create settings table

```bash
php artisan make:migration create_settings_table
```

Edit the generated file:

```php
public function up(): void
{
    Schema::create('settings', function (Blueprint $table) {
        $table->id();
        $table->string('key', 100)->unique();
        $table->text('value')->nullable();
        $table->string('type', 20)->default('string'); // string|boolean|integer|json
        $table->string('group', 50)->default('general');
        $table->string('label', 150)->nullable();
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('settings');
}
```

Then run: `php artisan migrate`

---

## STEP 2 — Seed default settings

```bash
php artisan make:seeder SettingsSeeder
```

Edit `database/seeders/SettingsSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── Sales Rules ────────────────────────────────────────────────
            [
                'key'         => 'allow_individual_item_sales',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'sales',
                'label'       => 'Allow individual item sales',
                'description' => 'When disabled, all products must be sold as full boxes only.',
            ],
            [
                'key'         => 'individual_sale_category_ids',
                'value'       => '[]',
                'type'        => 'json',
                'group'       => 'sales',
                'label'       => 'Categories allowed for individual item sales',
                'description' => 'Leave empty to allow all categories. Select specific categories to restrict individual sales to those only.',
            ],

            // ── Returns Policy ──────────────────────────────────────────────
            [
                'key'         => 'allow_seller_returns',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'returns',
                'label'       => 'Allow shop managers to process returns',
                'description' => 'When disabled, only the owner can process returns.',
            ],
            [
                'key'         => 'return_approval_threshold',
                'value'       => '100000',
                'type'        => 'integer',
                'group'       => 'returns',
                'label'       => 'Return approval threshold (RWF)',
                'description' => 'Returns with refund amount above this value require owner approval. Set to 0 to require approval for all returns.',
            ],
            [
                'key'         => 'max_return_days',
                'value'       => '30',
                'type'        => 'integer',
                'group'       => 'returns',
                'label'       => 'Maximum days after sale for returns',
                'description' => 'Returns cannot be processed for sales older than this many days. Set to 0 to disable the limit.',
            ],

            // ── Credit Policy ───────────────────────────────────────────────
            [
                'key'         => 'allow_credit_sales',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'credit',
                'label'       => 'Allow credit sales',
                'description' => 'When disabled, the credit payment channel is hidden in POS.',
            ],
            [
                'key'         => 'credit_requires_customer',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'credit',
                'label'       => 'Require registered customer for credit sales',
                'description' => 'When enabled, credit sales are blocked until a customer is selected.',
            ],
            [
                'key'         => 'max_credit_per_customer',
                'value'       => '0',
                'type'        => 'integer',
                'group'       => 'credit',
                'label'       => 'Maximum outstanding credit per customer (RWF)',
                'description' => 'Block new credit sales if customer already owes this amount. Set to 0 for no limit.',
            ],

            // ── Price Override ──────────────────────────────────────────────
            [
                'key'         => 'price_override_threshold',
                'value'       => '20',
                'type'        => 'integer',
                'group'       => 'price',
                'label'       => 'Price override approval threshold (%)',
                'description' => 'Price changes beyond this percentage require owner approval.',
            ],
            [
                'key'         => 'allow_price_override',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'price',
                'label'       => 'Allow sellers to modify prices',
                'description' => 'When disabled, sellers cannot change prices at all.',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
```

Run: `php artisan db:seed --class=SettingsSeeder`

---

## STEP 3 — Create Setting model

**File:** `app/Models/Setting.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description'];

    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json'    => json_decode($this->value, true) ?? [],
            default   => $this->value,
        };
    }
}
```

---

## STEP 4 — Create SettingsService

**File:** `app/Services/SettingsService.php`

```php
<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'app_settings';
    private const CACHE_TTL = 3600; // 1 hour

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();
        return $settings[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $setting = Setting::where('key', $key)->first();
        if (!$setting) return;

        // Serialize based on type
        $serialized = match ($setting->type) {
            'boolean' => $value ? 'true' : 'false',
            'json'    => json_encode($value),
            default   => (string) $value,
        };

        $setting->update(['value' => $serialized]);
        $this->clearCache();
    }

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::all()
                ->mapWithKeys(fn ($s) => [$s->key => $s->getTypedValue()])
                ->toArray();
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // ── Convenience helpers ───────────────────────────────────────────────

    public function allowIndividualItemSales(): bool
    {
        return (bool) $this->get('allow_individual_item_sales', true);
    }

    public function individualSaleCategoryIds(): array
    {
        return (array) $this->get('individual_sale_category_ids', []);
    }

    public function categoryAllowsIndividualSales(int $categoryId): bool
    {
        if (!$this->allowIndividualItemSales()) return false;
        $allowed = $this->individualSaleCategoryIds();
        return empty($allowed) || in_array($categoryId, $allowed);
    }

    public function allowSellerReturns(): bool
    {
        return (bool) $this->get('allow_seller_returns', true);
    }

    public function returnApprovalThreshold(): int
    {
        return (int) $this->get('return_approval_threshold', 100000);
    }

    public function maxReturnDays(): int
    {
        return (int) $this->get('max_return_days', 30);
    }

    public function allowCreditSales(): bool
    {
        return (bool) $this->get('allow_credit_sales', true);
    }

    public function creditRequiresCustomer(): bool
    {
        return (bool) $this->get('credit_requires_customer', true);
    }

    public function maxCreditPerCustomer(): int
    {
        return (int) $this->get('max_credit_per_customer', 0);
    }

    public function priceOverrideThreshold(): int
    {
        return (int) $this->get('price_override_threshold', 20);
    }

    public function allowPriceOverride(): bool
    {
        return (bool) $this->get('allow_price_override', true);
    }
}
```

---

## STEP 5 — Create Settings Livewire component

**File:** `app/Livewire/Owner/Settings.php`

```php
<?php

namespace App\Livewire\Owner;

use App\Models\Category;
use App\Services\SettingsService;
use Livewire\Component;

class Settings extends Component
{
    // Sales
    public bool  $allowIndividualItemSales  = true;
    public array $individualSaleCategoryIds = [];

    // Returns
    public bool $allowSellerReturns       = true;
    public int  $returnApprovalThreshold  = 100000;
    public int  $maxReturnDays            = 30;

    // Credit
    public bool $allowCreditSales       = true;
    public bool $creditRequiresCustomer = true;
    public int  $maxCreditPerCustomer   = 0;

    // Price
    public bool $allowPriceOverride       = true;
    public int  $priceOverrideThreshold   = 20;

    public function mount(): void
    {
        if (!auth()->user()->isOwner()) abort(403);

        $svc = app(SettingsService::class);

        $this->allowIndividualItemSales  = $svc->allowIndividualItemSales();
        $this->individualSaleCategoryIds = $svc->individualSaleCategoryIds();
        $this->allowSellerReturns        = $svc->allowSellerReturns();
        $this->returnApprovalThreshold   = $svc->returnApprovalThreshold();
        $this->maxReturnDays             = $svc->maxReturnDays();
        $this->allowCreditSales          = $svc->allowCreditSales();
        $this->creditRequiresCustomer    = $svc->creditRequiresCustomer();
        $this->maxCreditPerCustomer      = $svc->maxCreditPerCustomer();
        $this->allowPriceOverride        = $svc->allowPriceOverride();
        $this->priceOverrideThreshold    = $svc->priceOverrideThreshold();
    }

    public function save(): void
    {
        $this->validate([
            'returnApprovalThreshold' => 'required|integer|min:0',
            'maxReturnDays'           => 'required|integer|min:0',
            'maxCreditPerCustomer'    => 'required|integer|min:0',
            'priceOverrideThreshold'  => 'required|integer|min:1|max:100',
        ]);

        $svc = app(SettingsService::class);

        $svc->set('allow_individual_item_sales',  $this->allowIndividualItemSales);
        $svc->set('individual_sale_category_ids', $this->individualSaleCategoryIds);
        $svc->set('allow_seller_returns',         $this->allowSellerReturns);
        $svc->set('return_approval_threshold',    $this->returnApprovalThreshold);
        $svc->set('max_return_days',              $this->maxReturnDays);
        $svc->set('allow_credit_sales',           $this->allowCreditSales);
        $svc->set('credit_requires_customer',     $this->creditRequiresCustomer);
        $svc->set('max_credit_per_customer',      $this->maxCreditPerCustomer);
        $svc->set('allow_price_override',         $this->allowPriceOverride);
        $svc->set('price_override_threshold',     $this->priceOverrideThreshold);

        $this->dispatch('notification', [
            'type'    => 'success',
            'message' => 'Settings saved successfully',
        ]);
    }

    public function toggleCategory(int $categoryId): void
    {
        if (in_array($categoryId, $this->individualSaleCategoryIds)) {
            $this->individualSaleCategoryIds = array_values(
                array_filter($this->individualSaleCategoryIds, fn ($id) => $id !== $categoryId)
            );
        } else {
            $this->individualSaleCategoryIds[] = $categoryId;
        }
    }

    public function render()
    {
        return view('livewire.owner.settings', [
            'categories' => Category::with('parent')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
```

---

## STEP 6 — Create the settings blade view

**File:** `resources/views/livewire/owner/settings.blade.php`

```blade
<div style="font-family:var(--font);max-width:860px">
<style>
.cfg-section {
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r);margin-bottom:20px;overflow:hidden;
}
.cfg-section-head {
    padding:16px 22px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;gap:12px;
}
.cfg-section-icon {
    width:36px;height:36px;border-radius:9px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.cfg-section-title { font-size:14px;font-weight:700;color:var(--text) }
.cfg-section-sub   { font-size:11px;color:var(--text-dim);margin-top:1px }
.cfg-body { padding:0 }

.cfg-row {
    display:flex;align-items:flex-start;justify-content:space-between;
    padding:16px 22px;gap:20px;border-bottom:1px solid var(--border);
    transition:background var(--tr);
}
.cfg-row:last-child { border-bottom:none }
.cfg-row:hover { background:var(--surface2) }
.cfg-row-label { font-size:13px;font-weight:600;color:var(--text);margin-bottom:3px }
.cfg-row-desc  { font-size:11px;color:var(--text-dim);line-height:1.5;max-width:520px }
.cfg-row-control { flex-shrink:0 }

/* Toggle */
.cfg-toggle {
    position:relative;width:44px;height:24px;cursor:pointer;
    display:inline-block;
}
.cfg-toggle input { opacity:0;width:0;height:0;position:absolute }
.cfg-toggle-track {
    position:absolute;inset:0;border-radius:24px;
    background:var(--surface3);border:1px solid var(--border);
    transition:background var(--tr),border-color var(--tr);
}
.cfg-toggle input:checked ~ .cfg-toggle-track {
    background:var(--accent);border-color:var(--accent);
}
.cfg-toggle-thumb {
    position:absolute;top:3px;left:3px;
    width:18px;height:18px;border-radius:50%;
    background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);
    transition:transform var(--tr);
}
.cfg-toggle input:checked ~ .cfg-toggle-track .cfg-toggle-thumb {
    transform:translateX(20px);
}

/* Number input */
.cfg-input {
    width:140px;padding:7px 11px;border:1.5px solid var(--border);
    border-radius:8px;font-size:13px;font-weight:600;font-family:var(--mono);
    background:var(--surface);color:var(--text);outline:none;
    transition:border-color var(--tr);text-align:right;
}
.cfg-input:focus { border-color:var(--accent) }

/* Category checkboxes */
.cfg-cats {
    display:flex;flex-wrap:wrap;gap:7px;margin-top:10px;padding:12px 22px;
    border-top:1px solid var(--border);
}
.cfg-cat-chip {
    display:inline-flex;align-items:center;gap:6px;
    padding:5px 12px;border-radius:20px;cursor:pointer;
    border:1.5px solid var(--border);background:var(--surface2);
    font-size:12px;font-weight:600;color:var(--text-sub);
    transition:all var(--tr);user-select:none;
}
.cfg-cat-chip:hover { border-color:var(--accent);color:var(--accent) }
.cfg-cat-chip.selected {
    border-color:var(--accent);background:var(--accent-dim);color:var(--accent);
}
.cfg-cat-check {
    width:14px;height:14px;border-radius:4px;border:1.5px solid currentColor;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.cfg-cat-chip.selected .cfg-cat-check {
    background:var(--accent);border-color:var(--accent);
}

/* Save bar */
.cfg-save-bar {
    position:sticky;bottom:0;background:var(--surface);
    border-top:1px solid var(--border);
    padding:14px 22px;
    display:flex;align-items:center;justify-content:flex-end;gap:10px;
    box-shadow:0 -4px 16px rgba(26,31,54,.07);
    z-index:10;
}
.cfg-save-btn {
    padding:10px 28px;background:var(--accent);color:#fff;
    border:none;border-radius:10px;font-size:13px;font-weight:700;
    cursor:pointer;font-family:var(--font);
    box-shadow:0 4px 12px rgba(59,111,212,.25);
    transition:opacity var(--tr);
}
.cfg-save-btn:hover { opacity:.88 }

/* Mobile */
@media(max-width:600px) {
    .cfg-row { flex-direction:column;gap:10px }
    .cfg-input { width:100% }
    .cfg-cats { padding:10px 16px }
}
</style>

{{-- Page header --}}
<div style="margin-bottom:24px">
    <div style="font-size:20px;font-weight:800;color:var(--text);letter-spacing:-.3px">
        Business Settings
    </div>
    <div style="font-size:12px;color:var(--text-dim);margin-top:3px">
        Configure operational rules for your business
    </div>
</div>

{{-- ── SECTION 1: Sales Rules ───────────────────────────────────────── --}}
<div class="cfg-section">
    <div class="cfg-section-head">
        <div class="cfg-section-icon"
             style="background:var(--accent-dim);color:var(--accent)">
            <svg width="18" height="18" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
        </div>
        <div>
            <div class="cfg-section-title">Sales Rules</div>
            <div class="cfg-section-sub">
                Control how products can be sold at the shop
            </div>
        </div>
    </div>
    <div class="cfg-body">

        <div class="cfg-row">
            <div>
                <div class="cfg-row-label">Allow individual item sales</div>
                <div class="cfg-row-desc">
                    When enabled, sellers can sell individual items from a box.
                    When disabled, all products must be sold as full boxes only.
                </div>
            </div>
            <div class="cfg-row-control">
                <label class="cfg-toggle">
                    <input type="checkbox" wire:model.live="allowIndividualItemSales">
                    <div class="cfg-toggle-track">
                        <div class="cfg-toggle-thumb"></div>
                    </div>
                </label>
            </div>
        </div>

        @if($allowIndividualItemSales)
        <div class="cfg-row" style="flex-direction:column;gap:8px">
            <div>
                <div class="cfg-row-label">
                    Categories allowed for individual item sales
                </div>
                <div class="cfg-row-desc">
                    Select specific categories. Leave all unselected to allow
                    individual sales for every category.
                </div>
            </div>
            <div class="cfg-cats">
                @foreach($categories as $cat)
                @php $selected = in_array($cat->id, $individualSaleCategoryIds) @endphp
                <div wire:click="toggleCategory({{ $cat->id }})"
                     class="cfg-cat-chip {{ $selected ? 'selected' : '' }}">
                    <div class="cfg-cat-check">
                        @if($selected)
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none"
                             stroke="#fff" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        @endif
                    </div>
                    {{ $cat->name }}
                    @if($cat->parent)
                        <span style="font-size:10px;opacity:.6">
                            · {{ $cat->parent->name }}
                        </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ── SECTION 2: Returns Policy ────────────────────────────────────── --}}
<div class="cfg-section">
    <div class="cfg-section-head">
        <div class="cfg-section-icon"
             style="background:var(--violet-dim);color:var(--violet)">
            <svg width="18" height="18" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <polyline points="1 4 1 10 7 10"/>
                <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
            </svg>
        </div>
        <div>
            <div class="cfg-section-title">Returns Policy</div>
            <div class="cfg-section-sub">
                Control how and when returns are accepted
            </div>
        </div>
    </div>
    <div class="cfg-body">

        <div class="cfg-row">
            <div>
                <div class="cfg-row-label">Allow shop managers to process returns</div>
                <div class="cfg-row-desc">
                    When disabled, only the owner can process customer returns.
                </div>
            </div>
            <div class="cfg-row-control">
                <label class="cfg-toggle">
                    <input type="checkbox" wire:model="allowSellerReturns">
                    <div class="cfg-toggle-track">
                        <div class="cfg-toggle-thumb"></div>
                    </div>
                </label>
            </div>
        </div>

        <div class="cfg-row">
            <div>
                <div class="cfg-row-label">Return approval threshold (RWF)</div>
                <div class="cfg-row-desc">
                    Returns with a refund above this amount require owner approval.
                    Set to 0 to require approval for all returns.
                </div>
            </div>
            <div class="cfg-row-control">
                <input wire:model="returnApprovalThreshold"
                       type="number" min="0" class="cfg-input"
                       placeholder="100,000">
                @error('returnApprovalThreshold')
                    <div style="font-size:10px;color:var(--red);margin-top:4px">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="cfg-row">
            <div>
                <div class="cfg-row-label">Maximum days after sale for returns</div>
                <div class="cfg-row-desc">
                    Returns cannot be processed for sales older than this.
                    Set to 0 to allow returns at any time.
                </div>
            </div>
            <div class="cfg-row-control">
                <div style="display:flex;align-items:center;gap:8px">
                    <input wire:model="maxReturnDays"
                           type="number" min="0" class="cfg-input"
                           placeholder="30">
                    <span style="font-size:12px;color:var(--text-dim)">days</span>
                </div>
                @error('maxReturnDays')
                    <div style="font-size:10px;color:var(--red);margin-top:4px">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

    </div>
</div>

{{-- ── SECTION 3: Credit Policy ─────────────────────────────────────── --}}
<div class="cfg-section">
    <div class="cfg-section-head">
        <div class="cfg-section-icon"
             style="background:var(--amber-dim);color:var(--amber)">
            <svg width="18" height="18" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        <div>
            <div class="cfg-section-title">Credit Policy</div>
            <div class="cfg-section-sub">
                Control how credit sales work at the POS
            </div>
        </div>
    </div>
    <div class="cfg-body">

        <div class="cfg-row">
            <div>
                <div class="cfg-row-label">Allow credit sales</div>
                <div class="cfg-row-desc">
                    When disabled, the credit payment channel is completely hidden
                    in the POS checkout.
                </div>
            </div>
            <div class="cfg-row-control">
                <label class="cfg-toggle">
                    <input type="checkbox" wire:model.live="allowCreditSales">
                    <div class="cfg-toggle-track">
                        <div class="cfg-toggle-thumb"></div>
                    </div>
                </label>
            </div>
        </div>

        @if($allowCreditSales)
        <div class="cfg-row">
            <div>
                <div class="cfg-row-label">
                    Require registered customer for credit sales
                </div>
                <div class="cfg-row-desc">
                    When enabled, the credit channel is blocked until a customer
                    from the customer registry is selected. Strongly recommended.
                </div>
            </div>
            <div class="cfg-row-control">
                <label class="cfg-toggle">
                    <input type="checkbox" wire:model="creditRequiresCustomer">
                    <div class="cfg-toggle-track">
                        <div class="cfg-toggle-thumb"></div>
                    </div>
                </label>
            </div>
        </div>

        <div class="cfg-row">
            <div>
                <div class="cfg-row-label">
                    Maximum outstanding credit per customer (RWF)
                </div>
                <div class="cfg-row-desc">
                    Block new credit sales if the customer already owes this amount
                    or more. Set to 0 for no limit.
                </div>
            </div>
            <div class="cfg-row-control">
                <input wire:model="maxCreditPerCustomer"
                       type="number" min="0" class="cfg-input"
                       placeholder="0 = no limit">
                @error('maxCreditPerCustomer')
                    <div style="font-size:10px;color:var(--red);margin-top:4px">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ── SECTION 4: Price Override ────────────────────────────────────── --}}
<div class="cfg-section">
    <div class="cfg-section-head">
        <div class="cfg-section-icon"
             style="background:var(--green-dim);color:var(--green)">
            <svg width="18" height="18" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
            </svg>
        </div>
        <div>
            <div class="cfg-section-title">Price Override</div>
            <div class="cfg-section-sub">
                Control when sellers need approval to change prices
            </div>
        </div>
    </div>
    <div class="cfg-body">

        <div class="cfg-row">
            <div>
                <div class="cfg-row-label">Allow sellers to modify prices</div>
                <div class="cfg-row-desc">
                    When disabled, sellers cannot change prices at all.
                    Price is always what is set on the product.
                </div>
            </div>
            <div class="cfg-row-control">
                <label class="cfg-toggle">
                    <input type="checkbox" wire:model.live="allowPriceOverride">
                    <div class="cfg-toggle-track">
                        <div class="cfg-toggle-thumb"></div>
                    </div>
                </label>
            </div>
        </div>

        @if($allowPriceOverride)
        <div class="cfg-row">
            <div>
                <div class="cfg-row-label">
                    Approval threshold (%)
                </div>
                <div class="cfg-row-desc">
                    Price changes beyond this percentage require owner approval
                    before the sale can be completed.
                </div>
            </div>
            <div class="cfg-row-control">
                <div style="display:flex;align-items:center;gap:8px">
                    <input wire:model="priceOverrideThreshold"
                           type="number" min="1" max="100" class="cfg-input"
                           placeholder="20">
                    <span style="font-size:12px;color:var(--text-dim)">%</span>
                </div>
                @error('priceOverrideThreshold')
                    <div style="font-size:10px;color:var(--red);margin-top:4px">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Save bar --}}
<div class="cfg-save-bar">
    <span style="font-size:12px;color:var(--text-dim)">
        Changes take effect immediately after saving
    </span>
    <button wire:click="save"
            wire:loading.attr="disabled"
            wire:target="save"
            class="cfg-save-btn">
        <span wire:loading.remove wire:target="save">Save Settings</span>
        <span wire:loading wire:target="save">Saving…</span>
    </button>
</div>

</div>
```

---

## STEP 7 — Create the page wrapper blade

**File:** `resources/views/owner/settings.blade.php`

```blade
<x-app-layout>
    <div class="dashboard-page-header">
        <div>
            <h1>Settings</h1>
            <p>Business rules and operational configuration</p>
        </div>
    </div>
    <livewire:owner.settings />
</x-app-layout>
```

---

## STEP 8 — Register Livewire component

```bash
php artisan livewire:discover
```

Or verify `app/Livewire/Owner/Settings.php` is auto-discovered
(Laravel Livewire 3 auto-discovers by convention).

---

## STEP 9 — Wire settings into existing code

### A — POS: hide credit channel when disabled

In `app/Livewire/Shop/Sales/PointOfSale.php`, find `openCheckout()`.
Add at the top:

```php
$settings = app(\App\Services\SettingsService::class);
if (!$settings->allowCreditSales()) {
    $this->payAmt_credit = 0;
}
```

In the blade `resources/views/livewire/shop/sales/point-of-sale.blade.php`,
find the credit payment row. Wrap it:

```blade
@if(app(\App\Services\SettingsService::class)->allowCreditSales())
{{-- ... existing credit row ... --}}
@endif
```

### B — POS: enforce credit requires customer

In `PointOfSale::completeSale()`, find the credit validation block:

```php
if ($this->payAmt_credit > 0 && !$this->selectedCustomerId) {
```

Change the condition to always enforce when setting is on:

```php
$settings = app(\App\Services\SettingsService::class);
if ($this->payAmt_credit > 0
    && $settings->creditRequiresCustomer()
    && !$this->selectedCustomerId) {
```

### C — Returns: use settings thresholds

In `app/Livewire/Shop/Returns/ProcessReturn.php`, find `submitReturn()`.
Replace the hardcoded threshold:

```php
// BEFORE
if ($estimatedRefund > 50000 && !auth()->user()->isOwner()) {

// AFTER
$settings = app(\App\Services\SettingsService::class);
$threshold = $settings->returnApprovalThreshold();
if (!auth()->user()->isOwner()
    && !$settings->allowSellerReturns()) {
    session()->flash('error', 'Returns must be processed by the owner.');
    return;
}
if ($estimatedRefund > $threshold && !auth()->user()->isOwner()) {
```

Also add max return days check after sale is linked. In `goToStep(2)` or
wherever the sale age warning is evaluated, replace any hardcoded days:

```php
$maxDays = app(\App\Services\SettingsService::class)->maxReturnDays();
if ($maxDays > 0 && $saleAgeDays > $maxDays) {
    // hard block instead of warning
}
```

---

## STEP 10 — Clear caches

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

---

## Do NOT touch

- Any migration files already run
- The returns service business logic beyond the threshold values
- Any other Livewire components beyond the wiring in Step 9

---

## Verification

1. Visit `/owner/settings` — four sections load with correct defaults
2. Toggle "Allow credit sales" off → save → open POS →
   credit row is gone from the payment panel
3. Toggle back on → save → credit row reappears
4. Set `max_return_days = 7`, save → try to process a 30-day-old sale
   return → blocked with an error message
5. In tinker, confirm settings are cached:
   ```php
   app(\App\Services\SettingsService::class)->get('allow_credit_sales');
   // Should return true or false, not null
   ```
