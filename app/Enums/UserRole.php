<?php

namespace App\Enums;

enum UserRole: string
{
    case OWNER             = 'owner';
    case ADMIN             = 'admin';
    case WAREHOUSE_MANAGER = 'warehouse_manager';
    case SHOP_MANAGER      = 'shop_manager';

    public function label(): string
    {
        return match($this) {
            self::OWNER             => 'Owner',
            self::ADMIN             => 'Admin',
            self::WAREHOUSE_MANAGER => 'Warehouse Manager',
            self::SHOP_MANAGER      => 'Shop Manager',
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
                'request_transfers',
                'receive_transfers',
                'create_sales',
                'process_returns',
                'view_shop_reports',
                'manage_warehouse_inventory',
                'approve_transfers',
                'scan_boxes',
                'view_warehouse_reports',
            ],
            self::ADMIN => [
                // Full visibility (same as owner)
                'view_all_locations',
                'view_purchase_prices',
                'approve_price_overrides',
                'view_reports',
                'manage_settings',
                'view_all_activity_logs',

                // User management — warehouse/shop managers only, NOT owners
                'manage_users',

                // Inventory & operations
                'manage_products',
                'manage_warehouse_inventory',
                'approve_transfers',
                'scan_boxes',
                'view_warehouse_reports',
                'request_transfers',
                'receive_transfers',
                'create_sales',
                'process_returns',
                'view_shop_reports',
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
