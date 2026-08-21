<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'fulfillment_dispatch_method'],
            [
                'value'       => 'queue',
                'type'        => 'string',
                'group'       => 'fulfillment',
                'label'       => 'Fulfillment dispatch method',
                'description' => 'Choose whether warehouse staff dispatch pending pickup orders by scanning/typing a pickup code, or by confirming from a browsable list of orders awaiting pickup.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }

    public function down(): void
    {
        // Safe to leave — settings rows are additive
    }
};
