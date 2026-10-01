<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * New transfer status `ready`: packing is done and the transfer waits for
 * the transporter (between approved and in_transit).
 */
return new class extends Migration
{
    // ALTER TYPE … ADD VALUE must not run inside a transaction block on older Postgres
    public $withinTransaction = false;

    public function up(): void
    {
        DB::statement("ALTER TYPE transfer_status ADD VALUE IF NOT EXISTS 'ready' AFTER 'approved'");
    }

    public function down(): void
    {
        // Postgres can't drop an enum value; harmless to keep.
    }
};
