<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // insertOrIgnore: never overwrite an owner's existing choice on re-run
        DB::table('settings')->insertOrIgnore([
            'key'         => 'fulfillment_require_signature',
            'value'       => 'true',
            'type'        => 'boolean',
            'group'       => 'fulfillment',
            'label'       => 'Require signature on pickup',
            'description' => 'When on, the person collecting a warehouse-direct order must sign on screen before the dispatch can be confirmed. Their name is always recorded.',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        // Safe to leave — settings rows are additive
    }
};
