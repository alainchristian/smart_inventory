# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Smart Inventory** is a multi-location inventory and sales management system for retail/distribution operations. It manages warehouses, shops, product inventory tracking in boxes, inter-location transfers with full workflow, point-of-sale operations, returns, damaged goods, and comprehensive reporting.

## Development Commands

### Running the Application

```bash
# Start all services (server, queue, logs, vite) - Recommended
composer dev

# Or start services individually
php artisan serve              # Development server (localhost:8000)
npm run dev                    # Vite dev server with hot reload
php artisan queue:listen       # Queue worker
php artisan pail              # Real-time log viewer
```

### Asset Compilation

```bash
npm run dev          # Development with hot reload
npm run build        # Production build
```

### Database Operations

```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh        # Drop all tables and re-migrate
php artisan migrate:fresh --seed # Fresh migration with seeders
php artisan db:seed             # Run seeders only
```

### Code Quality

```bash
./vendor/bin/pint               # Format code (Laravel Pint)
./vendor/bin/phpstan analyse    # Static analysis (Larastan)
php artisan test                # Run PHPUnit tests
```

### Cache Management

```bash
php artisan config:clear        # Clear config cache
php artisan route:clear         # Clear route cache
php artisan view:clear          # Clear compiled views
php artisan cache:clear         # Clear application cache
php artisan optimize:clear      # Clear all caches
```

### Useful Development Commands

```bash
php artisan route:list          # List all registered routes
php artisan route:list --name=owner  # Filter routes by name
php artisan tinker              # Interactive REPL
php artisan make:livewire ComponentName  # Create Livewire component
```

## Architecture Overview

### Role-Based Access Control (RBAC)

The system has a **three-tier role hierarchy** defined in `app/Enums/UserRole.php`:

1. **Owner** - Full system access, manages all locations and users
2. **Warehouse Manager** - Warehouse-scoped access (inventory, approve transfers)
3. **Shop Manager** - Shop-scoped access (POS, request transfers, sales)

**Key Implementation Details:**

- User model has `location_type` and `location_id` (polymorphic to Warehouse or Shop)
- Middleware: `CheckRole` validates user role, `CheckLocation` enforces location-scoped access
- Each role has explicit permissions via `UserRole::permissions()` method
- Authorization uses Laravel Policies (`TransferPolicy`, `SalePolicy`, etc.)
- Helper methods on User: `hasPermission()`, `hasLocationAccess()`, `canManageLocation()`

**After authentication**, users are redirected by role:
- Owners → `/owner/dashboard`
- Warehouse Managers → `/warehouse/dashboard`
- Shop Managers → `/shop/dashboard`

See `LoginRedirectController` for redirect logic.

### Polymorphic Locations Pattern

**Critical Pattern**: Users, Boxes, and DamagedGoods can belong to either Warehouses or Shops using polymorphic relationships.

**Morph Map Configuration** in `AppServiceProvider::boot()`:
```php
Relation::enforceMorphMap([
    'warehouse' => \App\Models\Warehouse::class,
    'shop' => \App\Models\Shop::class,
]);
```

Models using this pattern:
- `User::location()` → morphTo Warehouse|Shop
- `Box::location()` → morphTo Warehouse|Shop
- `DamagedGood::location()` → morphTo Warehouse|Shop

Always use `LocationType` enum values ('warehouse', 'shop') when setting `location_type`.

### Inventory Architecture

**Box-Based Inventory System:**
- Products are received in boxes (`items_per_box` defined on Product)
- Each `Box` has a unique `box_code` and tracks `items_remaining`
- Box status: FULL, PARTIAL, DAMAGED, EMPTY (see `BoxStatus` enum)
- Inventory consumption via `Box::consumeItems()` method
- All movements logged immutably in `BoxMovement` model

**Stock Tracking:**
- Use `Product::getCurrentStock($locationType, $locationId)` to get inventory
- Returns: `['full_boxes' => int, 'partial_boxes' => int, 'total_items' => int]`
- Low stock detection: `Product::isLowStock($locationType, $locationId)`

### Transfer Workflow

**Status Progression:**
```
PENDING → APPROVED → IN_TRANSIT → DELIVERED → RECEIVED
         ↘ REJECTED
```

**Key Stages:**
1. **Request** (Shop Manager) - Creates transfer with `TransferItem` records
2. **Approve/Reject** (Warehouse Manager) - Validates request
3. **Pack** (Warehouse) - Assigns physical boxes via `TransferBox` records
4. **Ship** (Warehouse) - Scans boxes out, marks IN_TRANSIT, assigns transporter
5. **Deliver** (Transporter) - Marks DELIVERED
6. **Receive** (Shop Manager) - Scans boxes in, handles discrepancies, moves boxes to shop

**Service Layer:** All transfer operations MUST use `TransferService` methods to maintain data integrity.

### Sales & Point of Sale

**Sale Creation Flow:**
1. Shop Manager adds products/boxes to cart in `PointOfSale` Livewire component
2. On checkout, `SaleService::createSale()` handles:
   - Sale record creation with unique `sale_number`
   - `SaleItem` creation for each line item
   - Inventory consumption via `Box::consumeItems()`
   - Price override approval tracking
   - Event dispatching (`SaleCompleted`)

**Price Override Workflow:**
- If actual price ≠ original price, `has_price_override` flag set
- Requires Owner approval via `SaleService::approvePriceOverride()`
- Track via `price_override_approved_by` and `price_override_reason`

**Sale Voiding:**
- Use `SaleService::voidSale($sale, $reason)`
- Restores inventory to boxes
- Sets `voided_at`, `voided_by`, `void_reason`

### Monetary Values

**CRITICAL: All monetary values stored as integers (cents).**

Models with money fields:
- `Product`: `purchase_price`, `selling_price`, `box_selling_price`
- `Sale`/`SaleItem`: `subtotal`, `tax`, `discount`, `total`
- `DamagedGood`: `estimated_loss`
- `ReturnModel`: `refund_amount`

Use accessor methods to convert to dollars: `getPurchasePriceInDollarsAttribute()`.

**Currency:** This system uses **Rwanda Francs (RWF)** - display without decimal places using `number_format($value, 0)`.

### Audit Trail & Compliance

**Immutable Logs** (no updates, create-only):

1. **ActivityLog** - User actions audit trail
   - Tracks: user, action, entity, old/new values, IP, user agent
   - `UPDATED_AT` disabled

2. **BoxMovement** - Inventory movement audit
   - Tracks: from/to locations, movement type, user, reference transaction
   - `UPDATED_AT` disabled

**Alert System:**
- Critical events create `Alert` records (see `AlertSeverity` enum)
- Alerts can be: read, dismissed, or resolved
- Use scopes: `critical()`, `unresolved()`, `notDismissed()`

### Service Layer Pattern

**Complex business logic MUST use services:**

- `SaleService` - Sale creation, voiding, price override approval
- `TransferService` - Transfer workflow management
- `BarcodeService` - Barcode scanning operations

**Service Patterns:**
- Wrap operations in `DB::transaction()` for atomicity
- Dispatch domain events (`SaleCompleted`, `TransferReceived`)
- Generate sequential numbers (sale_number, transfer_number)
- Validate state transitions

### Livewire Component Architecture

**Component Organization:**
```
app/Livewire/
├── Owner/Dashboard.php              # Owner metrics
├── WarehouseManager/Dashboard.php   # Warehouse metrics
├── ShopManager/Dashboard.php        # Shop metrics
├── Inventory/
│   ├── Boxes/                       # Box management
│   └── Transfers/                   # Transfer workflow
├── Sales/PointOfSale.php           # Full POS system
└── Products/                        # Product CRUD
```

**Key Livewire Patterns:**
- Use `authorize()` in `mount()` to check permissions
- Dispatch browser events for UI updates (`sale-completed`, `barcode-scanned`)
- Real-time validation with `$rules` property
- Event listeners for barcode scanner integration

### Route Organization

Routes are organized by role in `routes/web.php`:

```
├── Public: / (redirects to login)
├── Auth: /login, /register, /forgot-password
├── Post-Login: /dashboard (redirects by role)
├── Owner: /owner/* (middleware: auth, CheckRole:owner)
├── Warehouse: /warehouse/* (middleware: auth, CheckRole:warehouse_manager, CheckLocation)
├── Shop: /shop/* (middleware: auth, CheckRole:shop_manager, CheckLocation)
└── Shared: /products/*, /profile (middleware: auth)
```

**Middleware Stack Pattern:**
```php
Route::middleware(['auth', CheckRole::class . ':role_name', CheckLocation::class])
```

### View Components

**Layout Structure:**
```
resources/views/components/layouts/
├── app.blade.php              # Default authenticated layout
├── guest.blade.php            # Login/auth pages
└── owner.blade.php            # Owner-specific layout with sidebar
```

**Owner Sidebar Component:** `resources/views/components/owner/sidebar.blade.php`
- Uses flexbox for fixed logout at bottom
- Scrollbar hidden via `.scrollbar-hide` CSS class
- JavaScript `toggleSubmenu()` for expandable menus

**Component Convention:**
- Blade components in `resources/views/components/`
- Use `<x-component-name>` syntax (hyphenated, not dot notation)
- Livewire components referenced as `<livewire:component-name />`

## Key Database Patterns

### Enums & Status Machines

All enums have `label()` and `color()` methods for UI rendering.

**Critical Enums:**
- `UserRole` - Role hierarchy with permissions
- `LocationType` - Warehouse|Shop (has `modelClass()` method)
- `TransferStatus` - Workflow state machine
- `BoxStatus` - Inventory status
- `SaleType` - FULL_BOX|INDIVIDUAL_ITEMS|MIXED
- `PaymentMethod` - Cash, card, mobile money, etc.
- `AlertSeverity` - INFO|WARNING|CRITICAL
- `DispositionType` - Damaged goods handling

### Model Scopes

Use query scopes for common filters:

```php
// Product scopes
Product::active()->lowStock($type, $id)->search($term)

// Box scopes
Box::atLocation($type, $id)->available()->expiringSoon(30)

// Transfer scopes
Transfer::pending()->forWarehouse($id)->recent(7)

// Sale scopes
Sale::notVoided()->dateRange($start, $end)->forShop($id)

// Alert scopes
Alert::critical()->unresolved()->notDismissed()->forUser($userId)
```

### Soft Deletes

Most models use soft deletes for data retention and audit compliance:
- User, Product, Box, Transfer, Sale, ReturnModel, DamagedGood, etc.
- Use `withTrashed()`, `onlyTrashed()` to query deleted records

## Important Conventions

### When Adding New Features

1. **Models:** Define relationships, scopes, and helper methods
2. **Policies:** Add authorization methods (viewAny, view, create, update, delete)
3. **Services:** Extract complex business logic into service classes
4. **Events:** Dispatch events for significant domain changes
5. **Livewire:** Use for interactive UIs (forms, real-time updates)
6. **Routes:** Group by role, apply appropriate middleware
7. **Migrations:** Use enums via PostgreSQL custom types (see existing migrations)

### When Modifying Workflows

1. Check existing service methods (don't bypass them)
2. Maintain state machine integrity (TransferStatus, BoxStatus)
3. Wrap multi-step operations in `DB::transaction()`
4. Create audit logs (ActivityLog, BoxMovement)
5. Dispatch events for workflow milestones
6. Update authorization policies

### Testing User Permissions

```php
// Check role
$user->isOwner()
$user->isWarehouseManager()
$user->isShopManager()

// Check specific permission
$user->hasPermission('approve_transfers')

// Check location access
$user->hasLocationAccess(LocationType::SHOP, $shopId)

// In policies
$user->can('approve', $transfer)
$user->can('create', Sale::class)
```

## Technology Stack

- **PHP:** 8.2+
- **Laravel:** 11.31
- **Livewire:** 3.6.4 (real-time components)
- **Volt:** 1.7.0 (single-file Livewire components)
- **Tailwind CSS:** 3.x (utility-first styling)
- **Vite:** 6.x (asset bundling)

**Additional Libraries:**
- `picqer/php-barcode-generator` - Barcode generation
- `barryvdh/laravel-dompdf` - PDF export
- `maatwebsite/excel` - Excel export
- `larastan/larastan` - PHPStan for Laravel
- `laravel/pint` - Code formatting

## Common Pitfalls

1. **Don't bypass services** - Always use `SaleService`, `TransferService` for complex operations
2. **Don't forget transactions** - Wrap multi-step DB operations in `DB::transaction()`
3. **Don't ignore location scoping** - Verify user has `hasLocationAccess()` before operations
4. **Don't store dollars** - All money values must be stored as cents (integers)
5. **Don't mutate audit logs** - ActivityLog and BoxMovement are immutable (create-only)
6. **Don't skip authorization** - Always check policies with `authorize()` in controllers/components
7. **Don't hardcode model classes** - Use morph map for polymorphic relations (warehouse/shop)
8. **Don't forget enum mappings** - Use LocationType enum, not string literals
9. **Don't create boxes without products** - Boxes must always reference a valid Product
10. **Don't modify transfer status directly** - Use TransferService methods to maintain workflow integrity
