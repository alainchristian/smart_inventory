# # Smart Inventory Management System - Windows Setup Script
# # PowerShell Version for Windows 10/11 - FIXED VERSION

# # Enable strict mode for better error handling
# $ErrorActionPreference = "Stop"

# # Define color functions at the top
# function Write-Success {
#     param([string]$Message)
#     Write-Host "✓ $Message" -ForegroundColor Green
# }

# function Write-InfoMessage {
#     param([string]$Message)
#     Write-Host "ℹ $Message" -ForegroundColor Blue
# }

# function Write-WarningMessage {
#     param([string]$Message)
#     Write-Host "⚠ $Message" -ForegroundColor Yellow
# }

# function Write-ErrorMessage {
#     param([string]$Message)
#     Write-Host "✗ $Message" -ForegroundColor Red
# }

# function Write-Section {
#     param([string]$Message)
#     Write-Host ""
#     Write-Host "=== $Message ===" -ForegroundColor Blue
#     Write-Host ""
# }

# Write-Host ""
# Write-Host "🚀 Smart Inventory Management System - Windows Setup Script" -ForegroundColor Cyan
# Write-Host "=============================================================" -ForegroundColor Cyan
# Write-Host ""

# Check if Laravel project exists
if (Test-Path "composer.json") {
    # Write-WarningMessage "Laravel project already exists in current directory"
    $response = Read-Host "Do you want to continue and add files to existing project? (y/n)"
    if ($response -ne "y" -and $response -ne "Y") {
        # Write-ErrorMessage "Setup cancelled"
        exit 1
    }
} else {
    # Write-ErrorMessage "This script should be run from the root of a Laravel 11 project"
    # Write-InfoMessage "Please run: composer create-project laravel/laravel smart-inventory '11.*'"
    # Write-InfoMessage "Then cd into the project directory and run this script again"
    exit 1
}

# =============================================================================
# PHASE 0: INSTALL DEPENDENCIES
# =============================================================================

# Write-Section "Installing Dependencies"

# Write-InfoMessage "Installing PHP dependencies via Composer..."
try {
    composer require livewire/livewire --quiet
    composer require picqer/php-barcode-generator --quiet
    composer require barryvdh/laravel-dompdf --quiet
    composer require maatwebsite/excel --quiet
    
    Write-InfoMessage "Installing development dependencies..."
    composer require --dev laravel/pint --quiet
    composer require --dev larastan/larastan --quiet
    
    # Write-Success "Dependencies installed"
} catch {
    # Write-ErrorMessage "Failed to install dependencies: $_"
    exit 1
}

# # =============================================================================
# # PHASE 1: CREATE DIRECTORY STRUCTURE
# # =============================================================================

# Write-Section "Creating Directory Structure"

# Write-InfoMessage "Creating Enums directory..."
# New-Item -ItemType Directory -Force -Path "app\Enums" | Out-Null

# Write-InfoMessage "Creating Services directories..."
# New-Item -ItemType Directory -Force -Path "app\Services\Inventory" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Services\Sales" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Services\Alerts" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Services\Reports" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Services\Cache" | Out-Null

# Write-InfoMessage "Creating Policies directories..."
# New-Item -ItemType Directory -Force -Path "app\Policies\Inventory" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Policies\Sales" | Out-Null

# Write-InfoMessage "Creating Livewire component directories..."
# New-Item -ItemType Directory -Force -Path "app\Livewire\Products" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Livewire\Inventory\Boxes" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Livewire\Inventory\Transfers" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Livewire\Sales" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Livewire\Reports" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Livewire\Settings" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Livewire\Admin" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Livewire\DamagedGoods" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Livewire\Returns" | Out-Null
# New-Item -ItemType Directory -Force -Path "app\Livewire\Shared" | Out-Null

# Write-InfoMessage "Creating view directories..."
# New-Item -ItemType Directory -Force -Path "resources\views\livewire\products" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\livewire\inventory\boxes" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\livewire\inventory\transfers" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\livewire\sales" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\livewire\reports" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\livewire\settings" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\livewire\admin" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\livewire\damaged-goods" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\livewire\returns" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\livewire\shared" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\layouts" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\components" | Out-Null
# New-Item -ItemType Directory -Force -Path "resources\views\pdfs" | Out-Null

# Write-InfoMessage "Creating test directories..."
# New-Item -ItemType Directory -Force -Path "tests\Feature\Inventory" | Out-Null
# New-Item -ItemType Directory -Force -Path "tests\Feature\Sales" | Out-Null
# New-Item -ItemType Directory -Force -Path "tests\Feature\Auth" | Out-Null
# New-Item -ItemType Directory -Force -Path "tests\Unit\Services" | Out-Null
# New-Item -ItemType Directory -Force -Path "tests\Unit\Models" | Out-Null

# Write-InfoMessage "Creating storage directories..."
# New-Item -ItemType Directory -Force -Path "storage\app\private\damaged-goods" | Out-Null
# New-Item -ItemType Directory -Force -Path "storage\app\private\receipts" | Out-Null
# New-Item -ItemType Directory -Force -Path "storage\app\public\barcodes" | Out-Null

# Write-Success "Directory structure created"

# =============================================================================
# PHASE 2: CREATE ENUM FILES
# =============================================================================

# Write-Section "Creating Enum Files"

# Write-InfoMessage "Creating UserRole enum..."
@'
<?php

namespace App\Enums;

enum UserRole: string
{
    case OWNER = 'owner';
    case WAREHOUSE_MANAGER = 'warehouse_manager';
    case SHOP_MANAGER = 'shop_manager';

    public function label(): string
    {
        return match($this) {
            self::OWNER => 'Owner',
            self::WAREHOUSE_MANAGER => 'Warehouse Manager',
            self::SHOP_MANAGER => 'Shop Manager',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::OWNER => [
                'view_all_locations',
                'manage_users',
                'view_purchase_prices',
                'approve_price_overrides',
                'manage_products',
                'view_reports',
                'manage_settings',
            ],
            self::WAREHOUSE_MANAGER => [
                'manage_warehouse_inventory',
                'approve_transfers',
                'scan_boxes',
                'view_warehouse_reports',
            ],
            self::SHOP_MANAGER => [
                'request_transfers',
                'receive_transfers',
                'create_sales',
                'process_returns',
                'view_shop_reports',
            ],
        };
    }
}
'@ | Out-File -FilePath "app\Enums\UserRole.php" -Encoding UTF8

# Write-InfoMessage "Creating LocationType enum..."
@'
<?php

namespace App\Enums;

enum LocationType: string
{
    case WAREHOUSE = 'warehouse';
    case SHOP = 'shop';

    public function label(): string
    {
        return match($this) {
            self::WAREHOUSE => 'Warehouse',
            self::SHOP => 'Shop',
        };
    }

    public function modelClass(): string
    {
        return match($this) {
            self::WAREHOUSE => \App\Models\Warehouse::class,
            self::SHOP => \App\Models\Shop::class,
        };
    }
}
'@ | Out-File -FilePath "app\Enums\LocationType.php" -Encoding UTF8

# Write-InfoMessage "Creating BoxStatus enum..."
@'
<?php

namespace App\Enums;

enum BoxStatus: string
{
    case FULL = 'full';
    case PARTIAL = 'partial';
    case DAMAGED = 'damaged';
    case EMPTY = 'empty';

    public function label(): string
    {
        return match($this) {
            self::FULL => 'Full',
            self::PARTIAL => 'Partial',
            self::DAMAGED => 'Damaged',
            self::EMPTY => 'Empty',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::FULL => 'green',
            self::PARTIAL => 'yellow',
            self::DAMAGED => 'red',
            self::EMPTY => 'gray',
        };
    }
}
'@ | Out-File -FilePath "app\Enums\BoxStatus.php" -Encoding UTF8

# Write-InfoMessage "Creating TransferStatus enum..."
@'
<?php

namespace App\Enums;

enum TransferStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::IN_TRANSIT => 'In Transit',
            self::DELIVERED => 'Delivered',
            self::RECEIVED => 'Received',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::APPROVED => 'blue',
            self::REJECTED => 'red',
            self::IN_TRANSIT => 'purple',
            self::DELIVERED => 'indigo',
            self::RECEIVED => 'green',
            self::CANCELLED => 'gray',
        };
    }
}
'@ | Out-File -FilePath "app\Enums\TransferStatus.php" -Encoding UTF8

# Write-InfoMessage "Creating SaleType enum..."
@'
<?php

namespace App\Enums;

enum SaleType: string
{
    case FULL_BOX = 'full_box';
    case INDIVIDUAL_ITEMS = 'individual_items';
    case MIXED = 'mixed';

    public function label(): string
    {
        return match($this) {
            self::FULL_BOX => 'Full Box',
            self::INDIVIDUAL_ITEMS => 'Individual Items',
            self::MIXED => 'Mixed',
        };
    }
}
'@ | Out-File -FilePath "app\Enums\SaleType.php" -Encoding UTF8

# Write-InfoMessage "Creating PaymentMethod enum..."
@'
<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case MOBILE_MONEY = 'mobile_money';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT = 'credit';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Cash',
            self::CARD => 'Card',
            self::MOBILE_MONEY => 'Mobile Money',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CREDIT => 'Credit',
        };
    }
}
'@ | Out-File -FilePath "app\Enums\PaymentMethod.php" -Encoding UTF8

# Write-InfoMessage "Creating ReturnReason enum..."
@'
<?php

namespace App\Enums;

enum ReturnReason: string
{
    case DEFECTIVE = 'defective';
    case WRONG_ITEM = 'wrong_item';
    case DAMAGED = 'damaged';
    case EXPIRED = 'expired';
    case CUSTOMER_REQUEST = 'customer_request';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::DEFECTIVE => 'Defective',
            self::WRONG_ITEM => 'Wrong Item',
            self::DAMAGED => 'Damaged',
            self::EXPIRED => 'Expired',
            self::CUSTOMER_REQUEST => 'Customer Request',
            self::OTHER => 'Other',
        };
    }
}
'@ | Out-File -FilePath "app\Enums\ReturnReason.php" -Encoding UTF8

# Write-InfoMessage "Creating AlertSeverity enum..."
@'
<?php

namespace App\Enums;

enum AlertSeverity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match($this) {
            self::INFO => 'Info',
            self::WARNING => 'Warning',
            self::CRITICAL => 'Critical',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::INFO => 'blue',
            self::WARNING => 'yellow',
            self::CRITICAL => 'red',
        };
    }
}
'@ | Out-File -FilePath "app\Enums\AlertSeverity.php" -Encoding UTF8

# Write-InfoMessage "Creating DispositionType enum..."
@'
<?php

namespace App\Enums;

enum DispositionType: string
{
    case PENDING = 'pending';
    case RETURN_TO_SUPPLIER = 'return_to_supplier';
    case DISPOSE = 'dispose';
    case DISCOUNT_SALE = 'discount_sale';
    case WRITE_OFF = 'write_off';
    case REPAIR = 'repair';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::RETURN_TO_SUPPLIER => 'Return to Supplier',
            self::DISPOSE => 'Dispose',
            self::DISCOUNT_SALE => 'Discount Sale',
            self::WRITE_OFF => 'Write Off',
            self::REPAIR => 'Repair',
        };
    }
}
'@ | Out-File -FilePath "app\Enums\DispositionType.php" -Encoding UTF8

# Write-Success "Enum files created (9 enums)"

# =============================================================================
# PHASE 3: CREATE HELPER SCRIPTS
# =============================================================================

# Write-Section "Creating Helper Scripts"

# Write-InfoMessage "Creating artisan-commands.bat..."
@'
# @echo off
# echo Creating all Laravel files...
# echo.

# echo Creating Migrations...
php artisan make:migration create_enum_types
php artisan make:migration create_users_table
php artisan make:migration create_warehouses_table
php artisan make:migration create_shops_table
php artisan make:migration create_transporters_table
php artisan make:migration create_categories_table
php artisan make:migration create_products_table
php artisan make:migration create_boxes_table
php artisan make:migration create_box_movements_table
php artisan make:migration create_transfers_table
php artisan make:migration create_transfer_items_table
php artisan make:migration create_transfer_boxes_table
php artisan make:migration create_sales_table
php artisan make:migration create_sale_items_table
php artisan make:migration create_returns_table
php artisan make:migration create_return_items_table
php artisan make:migration create_damaged_goods_table
php artisan make:migration create_activity_logs_table
php artisan make:migration create_alerts_table
php artisan make:migration create_inventory_snapshots_table
php artisan make:migration create_stock_levels_table

# echo.
# echo Creating Models...
php artisan make:model Warehouse
php artisan make:model Shop
php artisan make:model Transporter
php artisan make:model Category
php artisan make:model Product
php artisan make:model Box
php artisan make:model BoxMovement
php artisan make:model Transfer
php artisan make:model TransferItem
php artisan make:model TransferBox
php artisan make:model Sale
php artisan make:model SaleItem
php artisan make:model Return
php artisan make:model ReturnItem
php artisan make:model DamagedGood
php artisan make:model ActivityLog
php artisan make:model Alert
php artisan make:model InventorySnapshot
php artisan make:model StockLevel

# echo.
# echo Creating Policies...
php artisan make:policy ProductPolicy --model=Product
php artisan make:policy BoxPolicy --model=Box
php artisan make:policy TransferPolicy --model=Transfer
php artisan make:policy SalePolicy --model=Sale
php artisan make:policy ReturnPolicy --model=Return
php artisan make:policy DamagedGoodPolicy --model=DamagedGood

# echo.
# echo Creating Livewire Components...
php artisan make:livewire Products/ProductList
php artisan make:livewire Products/CreateProduct
php artisan make:livewire Inventory/Boxes/ReceiveBoxes
php artisan make:livewire Inventory/Transfers/TransferList
php artisan make:livewire Inventory/Transfers/RequestTransfer
php artisan make:livewire Inventory/Transfers/ReceiveTransfer
php artisan make:livewire Sales/PointOfSale
php artisan make:livewire Sales/SaleList
php artisan make:livewire Returns/ProcessReturn
php artisan make:livewire DamagedGoods/RecordDamage

# echo.
# echo Creating Factories...
php artisan make:factory WarehouseFactory
php artisan make:factory ShopFactory
php artisan make:factory ProductFactory
php artisan make:factory BoxFactory

# echo.
# echo Creating Seeders...
php artisan make:seeder WarehouseSeeder
php artisan make:seeder ShopSeeder
php artisan make:seeder ProductSeeder
php artisan make:seeder UserSeeder

echo.
echo ========================================
echo All files created successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Fill migration files from implementation guide
echo 2. Run: php artisan migrate
echo 3. Run: php artisan db:seed
echo 4. Start server: php artisan serve
'@ | Out-File -FilePath "artisan-commands.bat" -Encoding UTF8

# Write-Success "artisan-commands.bat created"

# =============================================================================
# PHASE 4: CREATE CONFIGURATION FILES
# =============================================================================

# Write-Section "Creating Configuration Files"

# Write-InfoMessage "Creating pint.json..."
@'
{
    "preset": "laravel",
    "rules": {
        "array_syntax": {"syntax": "short"},
        "binary_operator_spaces": {
            "default": "single_space"
        },
        "blank_line_after_opening_tag": true
    }
}
'@ | Out-File -FilePath "pint.json" -Encoding UTF8

# Write-InfoMessage "Creating phpstan.neon..."
@'
includes:
    - ./vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app
    level: 5
'@ | Out-File -FilePath "phpstan.neon" -Encoding UTF8

# Write-InfoMessage "Creating README.md..."
@'
# Smart Inventory Management System

Laravel 11 + Livewire + PostgreSQL inventory management system.

## Windows Quick Start

1. Run setup: `.\setup-smart-inventory.ps1`
2. Generate files: `.\artisan-commands.bat`
3. Setup database: Create `smart_inventory` in PostgreSQL
4. Configure .env with database credentials
5. Fill migrations from implementation guide
6. Run: `php artisan migrate`
7. Run: `php artisan serve`

Visit: http://localhost:8000

## Features

- Box-centric inventory tracking
- Barcode generation
- Multi-location management
- Transfer workflow
- POS system
- Returns & damaged goods
- Real-time alerts

## Documentation

See implementation guide documents for details.
'@ | Out-File -FilePath "README.md" -Encoding UTF8

Write-Success "Configuration files created"

# =============================================================================
# FINAL SUMMARY
# =============================================================================

Write-Section "Setup Complete!"

Write-Host ""
Write-Success "Project skeleton created successfully!"
Write-Host ""
Write-Host "Created:" -ForegroundColor Cyan
Write-Host "  ✓ All directories" -ForegroundColor Green
Write-Host "  ✓ 9 complete Enum files" -ForegroundColor Green
Write-Host "  ✓ artisan-commands.bat" -ForegroundColor Green
Write-Host "  ✓ Configuration files" -ForegroundColor Green
Write-Host ""

Write-Section "Next Steps"
Write-Host ""
Write-Host "1️⃣  Generate Laravel files:" -ForegroundColor Cyan
Write-Host "    .\artisan-commands.bat" -ForegroundColor Yellow
Write-Host ""
Write-Host "2️⃣  Setup PostgreSQL database:" -ForegroundColor Cyan
Write-Host "    Create database 'smart_inventory' in pgAdmin or psql" -ForegroundColor Yellow
Write-Host ""
Write-Host "3️⃣  Configure environment:" -ForegroundColor Cyan
Write-Host "    Copy .env.example to .env" -ForegroundColor Yellow
Write-Host "    Update DB_* settings" -ForegroundColor Yellow
Write-Host ""
Write-Host "4️⃣  Fill migration files:" -ForegroundColor Cyan
Write-Host "    Copy schemas from implementation guide Part 1" -ForegroundColor Yellow
Write-Host ""
Write-Host "5️⃣  Run migrations:" -ForegroundColor Cyan
Write-Host "    php artisan migrate" -ForegroundColor Yellow
Write-Host ""
Write-Host "6️⃣  Start development:" -ForegroundColor Cyan
Write-Host "    php artisan serve" -ForegroundColor Yellow
Write-Host ""
Write-Host "✨ Happy coding! ✨" -ForegroundColor Magenta
Write-Host ""
