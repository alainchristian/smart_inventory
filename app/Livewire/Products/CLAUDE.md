## Box-Centric Product Management & Owner Stock Intake — completed 2026-05-14

### What changed

**Owner is now solely responsible for the product catalogue and all stock intake.
Warehouse managers manage and ship existing boxes; they do not create products.**

### Pricing redesign (box-centric)

Products are priced at the **box level** in all forms. Per-item prices are derived automatically.

| Form field | DB column written | Formula |
|---|---|---|
| Box Purchase Price | `products.purchase_price` | `round(boxPurchasePrice / items_per_box)` |
| Box Selling Price | `products.selling_price` | `round(boxSellingPrice / items_per_box)` |
| Box Selling Price | `products.box_selling_price` | stored as-is (always populated now) |

On edit/load: form shows `purchase_price × items_per_box` and `box_selling_price ?? selling_price × items_per_box`.

**DB schema unchanged** — `purchase_price` and `selling_price` remain per-item integers. POS, analytics, and transfer logic are unaffected.

### Files changed

| File | Change |
|---|---|
| `resources/views/livewire/products/_form.blade.php` | Pricing card: Items/Box first, then Box Purchase Price + Box Selling Price (2-col). Per-item hint shown below each field. |
| `app/Livewire/Products/CreateProduct.php` | Props: `boxPurchasePrice`, `boxSellingPrice`. `save()` computes per-item. Flash has "Add stock →" link. |
| `app/Livewire/Products/EditProduct.php` | `mount()` converts DB per-item → box prices. `update()` same as create. |
| `app/Policies/BoxPolicy.php` | `create()` now returns `true` for owner and warehouse_manager (was always `false`). |
| `app/Livewire/Warehouse/Inventory/ReceiveBoxes.php` | `mount()` handles `?product_id=X` query string — pre-fills product + opens dropdown. Removed debug `\Log::info`. |

### Owner Stock Intake

New route for the owner to receive supplier stock directly into a warehouse.

| Item | Value |
|---|---|
| Route | `owner.inventory.receive` → `GET /owner/inventory/receive` |
| View (wrapper) | `resources/views/owner/inventory/receive.blade.php` |
| Livewire component | `<livewire:warehouse.inventory.receive-boxes />` (same as WM page) |
| Sidebar | "Receive Stock" link after "All Boxes" in owner nav |

The owner page embeds `App\Livewire\Warehouse\Inventory\ReceiveBoxes` — identical UI to `/warehouse/inventory/boxes/receive` (barcode scan, Excel import, product creation, recent boxes table). The warehouse route already allowed owners via `CheckRole::class . ':warehouse_manager,owner'`; `CheckLocation` passes owners through unconditionally.

After creating a product, the flash message includes a direct "Add stock →" link that pre-fills `?product_id=X` on the intake page.

### Key rules

- **Never enter per-item prices directly** in the product form — always enter box prices; the form computes per-item on save.
- `box_selling_price` is now **always set** when creating or editing a product (previously optional override). Existing products with `box_selling_price = null` still work via `effective_box_selling_price` accessor.
- The simple `App\Livewire\Inventory\Boxes\ReceiveBoxes` component still exists but is not used by any current page — do not route to it.
- `App\Livewire\Owner\Products\CreateProduct` is an older orphaned component — never route to it.
