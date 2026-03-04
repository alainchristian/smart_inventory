<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update column comments only — data was already re-seeded as real RWF
        // If you have existing cent data: UPDATE products SET purchase_price = purchase_price / 100, etc.

        DB::statement("COMMENT ON COLUMN products.purchase_price IS 'Price in RWF (whole number)'");
        DB::statement("COMMENT ON COLUMN products.selling_price IS 'Price per item in RWF (whole number)'");
        DB::statement("COMMENT ON COLUMN products.box_selling_price IS 'Full box price in RWF (whole number)'");
        DB::statement("COMMENT ON COLUMN sales.subtotal IS 'Amount in RWF'");
        DB::statement("COMMENT ON COLUMN sales.tax IS 'Tax amount in RWF'");
        DB::statement("COMMENT ON COLUMN sales.discount IS 'Discount amount in RWF'");
        DB::statement("COMMENT ON COLUMN sales.total IS 'Total amount in RWF'");
        DB::statement("COMMENT ON COLUMN sale_items.original_unit_price IS 'Price in RWF'");
        DB::statement("COMMENT ON COLUMN sale_items.actual_unit_price IS 'Price in RWF'");
        DB::statement("COMMENT ON COLUMN sale_items.line_total IS 'Amount in RWF'");
    }

    public function down(): void
    {
        // No destructive changes
    }
};
