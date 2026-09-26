<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Boxes packed onto a warehouse → shop transfer used to stay full/partial at
 * the warehouse until the shop received them, so they could still be sold
 * there (or packed onto another transfer). They now go to box status
 * in_transit when packed; the status they had is kept here so a cancel or
 * receipt can restore it.
 *
 * Backfill: boxes on transfers that are packed but not yet received or
 * cancelled are put on hold now.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfer_boxes', function (Blueprint $table) {
            $table->string('box_status_before', 20)->nullable()->after('box_id');
        });

        $held = DB::table('transfer_boxes')
            ->join('transfers', 'transfers.id', '=', 'transfer_boxes.transfer_id')
            ->join('boxes', 'boxes.id', '=', 'transfer_boxes.box_id')
            ->whereIn('transfers.status', ['approved', 'in_transit', 'delivered'])
            ->where('transfer_boxes.is_received', false)
            ->where('boxes.location_type', 'warehouse')
            ->whereIn('boxes.status', ['full', 'partial'])
            ->get(['transfer_boxes.id as tb_id', 'boxes.id as box_id', 'boxes.status']);

        foreach ($held as $row) {
            DB::table('transfer_boxes')->where('id', $row->tb_id)->update(['box_status_before' => $row->status]);
            DB::table('boxes')->where('id', $row->box_id)->update(['status' => 'in_transit', 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Put held boxes back on sale before dropping the column
        DB::table('transfer_boxes')
            ->join('boxes', 'boxes.id', '=', 'transfer_boxes.box_id')
            ->join('transfers', 'transfers.id', '=', 'transfer_boxes.transfer_id')
            ->whereIn('transfers.status', ['approved', 'in_transit', 'delivered'])
            ->where('transfer_boxes.is_received', false)
            ->whereNotNull('transfer_boxes.box_status_before')
            ->where('boxes.status', 'in_transit')
            ->get(['boxes.id', 'transfer_boxes.box_status_before'])
            ->each(function ($r) {
                DB::table('boxes')->where('id', $r->id)->update(['status' => $r->box_status_before]);
            });

        Schema::table('transfer_boxes', function (Blueprint $table) {
            $table->dropColumn('box_status_before');
        });
    }
};
