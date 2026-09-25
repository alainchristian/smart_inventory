<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * individual_sale_category_ids used to mean "empty = every category may sell
 * by item". It is now opt-in ("empty = every category sells by full box").
 * To keep today's behaviour unchanged, a shop that had the switch ON with
 * nothing ticked gets every existing category ticked.
 */
return new class extends Migration
{
    public function up(): void
    {
        $switch = DB::table('settings')->where('key', 'allow_individual_item_sales')->value('value');
        $ids    = json_decode((string) DB::table('settings')->where('key', 'individual_sale_category_ids')->value('value'), true) ?: [];

        $switchOn = $switch === null || in_array(strtolower((string) $switch), ['true', '1'], true);

        if ($switchOn && $ids === []) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'individual_sale_category_ids'],
                [
                    'value'      => json_encode(DB::table('categories')->whereNull('deleted_at')->orderBy('id')->pluck('id')->map(fn ($i) => (int) $i)->all()),
                    'type'       => 'json',
                    'group'      => 'sales',
                    'updated_at' => now(),
                ]
            );
        }

        DB::table('settings')->where('key', 'individual_sale_category_ids')->update([
            'description' => 'Only the selected categories can be sold by individual item. All other categories are sold by full box only.',
        ]);

        Cache::forget('app_settings');
    }

    public function down(): void
    {
        // Leave the selection as-is (it's still a valid opt-in list)
    }
};
