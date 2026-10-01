<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // insertOrIgnore: never overwrite an owner's existing choice on re-run
        DB::table('settings')->insertOrIgnore([
            'key'         => 'transfer_require_signature',
            'value'       => 'true',
            'type'        => 'boolean',
            'group'       => 'transfers',
            'label'       => "Require the driver's signature at dispatch",
            'description' => 'When on, whoever takes a transfer from the warehouse signs on screen before it can be dispatched. Their name is always recorded.',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        // Safe to leave — settings rows are additive
    }
};
