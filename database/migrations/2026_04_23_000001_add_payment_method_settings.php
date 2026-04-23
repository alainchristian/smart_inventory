<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key'         => 'allow_card_payment',
                'value'       => 'false',
                'type'        => 'boolean',
                'description' => 'Allow card payment at point of sale',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'allow_bank_transfer_payment',
                'value'       => 'false',
                'type'        => 'boolean',
                'description' => 'Allow bank transfer payment at point of sale',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        foreach ($settings as $setting) {
            if (!DB::table('settings')->where('key', $setting['key'])->exists()) {
                DB::table('settings')->insert($setting);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['allow_card_payment', 'allow_bank_transfer_payment'])->delete();
    }
};
