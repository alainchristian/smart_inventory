<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Transfer process (2026-10-01): who and when for every step, the dispatch
 * hand-over, per-box discrepancy resolution, and a history table
 * (transfer_events) that every step writes to.
 *
 * Backfill:
 * - quantity_approved = quantity_requested for transfers already past
 *   approval (the shop's original numbers were overwritten before; from now
 *   on approval writes quantity_approved and leaves quantity_requested).
 * - transfer_events from the timestamps transfers already carry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->date('needed_by')->nullable()->after('requested_at');
            $table->timestamp('packing_done_at')->nullable()->after('packed_at');
            $table->foreignId('packing_done_by')->nullable()->after('packing_done_at')->constrained('users')->nullOnDelete();
            $table->foreignId('shipped_by')->nullable()->after('shipped_at')->constrained('users')->nullOnDelete();
            $table->string('handed_to_name', 120)->nullable()->after('shipped_by');
            $table->text('handover_signature')->nullable()->after('handed_to_name');
            $table->text('transporter_instructions')->nullable()->after('handover_signature');
            $table->timestamp('expected_arrival_at')->nullable()->after('transporter_instructions');
            $table->foreignId('delivered_by')->nullable()->after('delivered_at')->constrained('users')->nullOnDelete();
            $table->string('received_by_name', 120)->nullable()->after('received_at');
            $table->text('receipt_signature')->nullable()->after('received_by_name');
            $table->timestamp('cancelled_at')->nullable()->after('received_by_name');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable()->after('cancelled_by');
        });

        Schema::table('transfer_items', function (Blueprint $table) {
            $table->integer('quantity_approved')->nullable()->after('quantity_requested');
            $table->string('short_reason', 255)->nullable()->after('discrepancy_reason');
        });

        Schema::table('transfer_boxes', function (Blueprint $table) {
            // found | lost | received_late
            $table->string('resolution', 20)->nullable()->after('damage_notes');
            $table->foreignId('resolved_by')->nullable()->after('resolution')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            $table->text('resolution_notes')->nullable()->after('resolved_at');
        });

        Schema::create('transfer_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained()->cascadeOnDelete();
            $table->string('action', 40);
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name', 120)->nullable();
            $table->text('note')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['transfer_id', 'created_at']);
        });

        $this->backfill();
    }

    public function backfill(): void
    {
        DB::table('transfer_items')
            ->whereNull('quantity_approved')
            ->whereIn('transfer_id', DB::table('transfers')->whereNotIn('status', ['pending', 'rejected'])->select('id'))
            ->update(['quantity_approved' => DB::raw('quantity_requested')]);

        // Received transfers with nothing missing are already closed.
        DB::table('transfers')->where('status', 'received')->whereNull('closed_at')->where('has_discrepancy', false)
            ->update(['closed_at' => DB::raw('received_at')]);

        $names = DB::table('users')->pluck('name', 'id');
        DB::table('transfers')->whereNotIn('id', DB::table('transfer_events')->select('transfer_id'))
            ->orderBy('id')
            ->each(function ($t) use ($names) {
                $rows = [];
                $add = function (string $action, $at, $by, ?string $from, ?string $to, ?string $note = null) use (&$rows, $t, $names) {
                    if (! $at) {
                        return;
                    }
                    $rows[] = [
                        'transfer_id' => $t->id, 'action' => $action, 'from_status' => $from, 'to_status' => $to,
                        'user_id' => $by, 'user_name' => $by ? ($names[$by] ?? null) : null, 'note' => $note,
                        'meta' => json_encode(['backfilled' => true]), 'created_at' => $at,
                    ];
                };
                $add('requested', $t->requested_at ?? $t->created_at, $t->requested_by, null, 'pending', $t->notes);
                if ($t->status === 'rejected') {
                    $add('rejected', $t->reviewed_at, $t->reviewed_by, 'pending', 'rejected', $t->review_notes);
                } else {
                    $add('approved', $t->reviewed_at, $t->reviewed_by, 'pending', 'approved', $t->review_notes);
                }
                $add('packing_started', $t->packed_at, $t->packed_by, null, null);
                $add('dispatched', $t->shipped_at, null, 'approved', 'in_transit');
                $add('arrived', $t->delivered_at, null, 'in_transit', 'delivered');
                $add('received', $t->received_at, $t->received_by, 'delivered', 'received');
                if ($t->status === 'cancelled') {
                    $add('cancelled', $t->updated_at, null, null, 'cancelled', $t->review_notes);
                }
                if ($rows) {
                    DB::table('transfer_events')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_events');
        Schema::table('transfer_boxes', fn (Blueprint $t) => $t->dropConstrainedForeignId('resolved_by'));
        Schema::table('transfer_boxes', fn (Blueprint $t) => $t->dropColumn(['resolution', 'resolved_at', 'resolution_notes']));
        Schema::table('transfer_items', fn (Blueprint $t) => $t->dropColumn(['quantity_approved', 'short_reason']));
        Schema::table('transfers', function (Blueprint $t) {
            foreach (['packing_done_by', 'shipped_by', 'delivered_by', 'cancelled_by'] as $fk) {
                $t->dropConstrainedForeignId($fk);
            }
            $t->dropColumn(['needed_by', 'packing_done_at', 'handed_to_name', 'handover_signature', 'transporter_instructions',
                'expected_arrival_at', 'received_by_name', 'receipt_signature', 'cancelled_at', 'closed_at']);
        });
    }
};
