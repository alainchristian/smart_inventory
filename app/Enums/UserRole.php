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
                // Original owner permissions
                'view_all_locations',
                'manage_users',
                'view_purchase_prices',
                'approve_price_overrides',
                'manage_products',
                'view_reports',
                'manage_settings',
                // Shop Manager permissions
                'request_transfers',
                'receive_transfers',
                'create_sales',
                'process_returns',
                'view_shop_reports',
                // Warehouse Manager permissions
                'manage_warehouse_inventory',
                'approve_transfers',
                'scan_boxes',
                'view_warehouse_reports',
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
