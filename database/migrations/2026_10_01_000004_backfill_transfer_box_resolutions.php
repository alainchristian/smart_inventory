<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Before 2026-10-01 a received transfer put its missing boxes straight back
 * on sale at the warehouse and left damaged boxes there as `damaged`, with
 * nothing to resolve. Mark those boxes resolved (legacy) so old transfers
 * don't show as open issues, and close the transfers.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->backfill();
    }

    public function backfill(): void
    {
        $received = DB::table('transfers')->where('status', 'received')->select('id');

        DB::table('transfer_boxes')->whereIn('transfer_id', $received)->whereNull('resolution')
            ->where('is_received', false)
            ->update(['resolution' => 'found', 'resolved_at' => DB::raw('updated_at'),
                'resolution_notes' => 'Put back on sale at the warehouse on receipt (before discrepancy resolution existed).']);

        DB::table('transfer_boxes')->whereIn('transfer_id', $received)->whereNull('resolution')
            ->where('is_damaged', true)
            ->update(['resolution' => 'damaged_goods', 'resolved_at' => DB::raw('updated_at'),
                'resolution_notes' => 'Marked damaged on receipt (before discrepancy resolution existed).']);

        DB::table('transfers')->where('status', 'received')->whereNull('closed_at')
            ->update(['closed_at' => DB::raw('received_at')]);
    }

    public function down(): void
    {
        // Data backfill only.
    }
};
