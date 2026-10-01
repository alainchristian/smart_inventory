<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** How long a transfer may wait at each stage before alerts:generate raises an overdue alert. */
return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['transfer_alert_pack_hours', '24', 'Hours to pack and dispatch', 'Approved transfers not packed, or packed and not dispatched, after this many hours raise an alert.'],
            ['transfer_alert_transit_hours', '48', 'Hours on the road', 'A dispatched transfer is overdue after its expected arrival time, or after this many hours when none was given.'],
            ['transfer_alert_receive_hours', '12', 'Hours to scan in', 'A transfer marked arrived but not received after this many hours raises an alert.'],
        ];
        foreach ($rows as [$key, $value, $label, $description]) {
            // insertOrIgnore: never overwrite an owner's existing choice on re-run
            DB::table('settings')->insertOrIgnore([
                'key' => $key, 'value' => $value, 'type' => 'integer', 'group' => 'transfers',
                'label' => $label, 'description' => $description, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Safe to leave — settings rows are additive
    }
};
