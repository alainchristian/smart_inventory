<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * transfers.notes is the shop's request note. Approving with a note,
 * rejecting and cancelling used to overwrite (or append to) it, so the
 * shop's instructions were lost. Those now go to review_notes.
 *
 * Backfill what can be split back apart:
 * - rejected: notes held only the rejection reason (the request note was
 *   already overwritten) → move it to review_notes.
 * - cancelled: notes was "<request note>\n\nCancelled: <reason>" → split.
 * Approved transfers can't be told apart (the approval note replaced the
 * request note), so they're left as they are.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->text('review_notes')->nullable()->after('notes');
        });

        $this->backfill();
    }

    /** Idempotent: only touches rows whose review_notes is still empty. */
    public function backfill(): void
    {
        DB::table('transfers')
            ->where('status', 'rejected')
            ->whereNotNull('notes')
            ->whereNull('review_notes')
            ->update(['review_notes' => DB::raw('notes'), 'notes' => null]);

        DB::table('transfers')
            ->where('status', 'cancelled')
            ->where('notes', 'like', '%Cancelled: %')
            ->whereNull('review_notes')
            ->orderBy('id')
            ->each(function ($t) {
                $at = strrpos($t->notes, 'Cancelled: ');
                $request = rtrim(substr($t->notes, 0, $at));
                DB::table('transfers')->where('id', $t->id)->update([
                    'notes'        => $request !== '' ? $request : null,
                    'review_notes' => substr($t->notes, $at + strlen('Cancelled: ')),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropColumn('review_notes');
        });
    }
};
