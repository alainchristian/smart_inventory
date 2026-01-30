// database/migrations/2026_01_23_000001_create_enum_types.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // User role enum
        DB::statement("DO $$ BEGIN CREATE TYPE user_role AS ENUM ('owner', 'warehouse_manager', 'shop_manager'); EXCEPTION WHEN duplicate_object THEN null; END $$;");

        // Location type enum
        DB::statement("DO $$ BEGIN CREATE TYPE location_type AS ENUM ('warehouse', 'shop'); EXCEPTION WHEN duplicate_object THEN null; END $$;");

        // Box status enum
        DB::statement("DO $$ BEGIN CREATE TYPE box_status AS ENUM ('full', 'partial', 'damaged', 'empty'); EXCEPTION WHEN duplicate_object THEN null; END $$;");

        // Transfer status enum
        DB::statement("DO $$ BEGIN CREATE TYPE transfer_status AS ENUM ('pending', 'approved', 'rejected', 'in_transit', 'delivered', 'received', 'cancelled'); EXCEPTION WHEN duplicate_object THEN null; END $$;");

        // Sale type enum
        DB::statement("DO $$ BEGIN CREATE TYPE sale_type AS ENUM ('full_box', 'individual_items', 'mixed'); EXCEPTION WHEN duplicate_object THEN null; END $$;");

        // Payment method enum
        DB::statement("DO $$ BEGIN CREATE TYPE payment_method AS ENUM ('cash', 'card', 'mobile_money', 'bank_transfer', 'credit'); EXCEPTION WHEN duplicate_object THEN null; END $$;");

        // Return reason enum
        DB::statement("DO $$ BEGIN CREATE TYPE return_reason AS ENUM ('defective', 'wrong_item', 'damaged', 'expired', 'customer_request', 'other'); EXCEPTION WHEN duplicate_object THEN null; END $$;");

        // Alert severity enum
        DB::statement("DO $$ BEGIN CREATE TYPE alert_severity AS ENUM ('info', 'warning', 'critical'); EXCEPTION WHEN duplicate_object THEN null; END $$;");

        // Disposition type enum
        DB::statement("DO $$ BEGIN CREATE TYPE disposition_type AS ENUM ('pending', 'return_to_supplier', 'dispose', 'discount_sale', 'write_off', 'repair'); EXCEPTION WHEN duplicate_object THEN null; END $$;");
    }

    public function down(): void
    {
        DB::statement("DROP TYPE IF EXISTS user_role CASCADE");
        DB::statement("DROP TYPE IF EXISTS location_type CASCADE");
        DB::statement("DROP TYPE IF EXISTS box_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS transfer_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS sale_type CASCADE");
        DB::statement("DROP TYPE IF EXISTS payment_method CASCADE");
        DB::statement("DROP TYPE IF EXISTS return_reason CASCADE");
        DB::statement("DROP TYPE IF EXISTS alert_severity CASCADE");
        DB::statement("DROP TYPE IF EXISTS disposition_type CASCADE");
    }
};