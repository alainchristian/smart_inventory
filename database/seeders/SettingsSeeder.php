<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── Sales Rules ────────────────────────────────────────────────
            [
                'key'         => 'allow_individual_item_sales',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'sales',
                'label'       => 'Allow individual item sales',
                'description' => 'When disabled, all products must be sold as full boxes only.',
            ],
            [
                'key'         => 'individual_sale_category_ids',
                'value'       => '[]',
                'type'        => 'json',
                'group'       => 'sales',
                'label'       => 'Categories allowed for individual item sales',
                'description' => 'Leave empty to allow all categories. Select specific categories to restrict individual sales to those only.',
            ],

            // ── Returns Policy ──────────────────────────────────────────────
            [
                'key'         => 'allow_seller_returns',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'returns',
                'label'       => 'Allow shop managers to process returns',
                'description' => 'When disabled, only the owner can process returns.',
            ],
            [
                'key'         => 'return_approval_threshold',
                'value'       => '100000',
                'type'        => 'integer',
                'group'       => 'returns',
                'label'       => 'Return approval threshold (RWF)',
                'description' => 'Returns with refund amount above this value require owner approval. Set to 0 to require approval for all returns.',
            ],
            [
                'key'         => 'max_return_days',
                'value'       => '30',
                'type'        => 'integer',
                'group'       => 'returns',
                'label'       => 'Maximum days after sale for returns',
                'description' => 'Returns cannot be processed for sales older than this many days. Set to 0 to disable the limit.',
            ],

            // ── Credit Policy ───────────────────────────────────────────────
            [
                'key'         => 'allow_credit_sales',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'credit',
                'label'       => 'Allow credit sales',
                'description' => 'When disabled, the credit payment channel is hidden in POS.',
            ],
            [
                'key'         => 'credit_requires_customer',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'credit',
                'label'       => 'Require registered customer for credit sales',
                'description' => 'When enabled, credit sales are blocked until a customer is selected.',
            ],
            [
                'key'         => 'max_credit_per_customer',
                'value'       => '0',
                'type'        => 'integer',
                'group'       => 'credit',
                'label'       => 'Maximum outstanding credit per customer (RWF)',
                'description' => 'Block new credit sales if customer already owes this amount. Set to 0 for no limit.',
            ],
            [
                'key'         => 'overdue_credit_days',
                'value'       => '14',
                'type'        => 'integer',
                'group'       => 'credit',
                'label'       => 'Overdue credit threshold (days)',
                'description' => 'Flag a customer as overdue if they have an outstanding balance and no repayment activity in this many days. Set to 0 to disable overdue flagging.',
            ],

            // ── Price Override ──────────────────────────────────────────────
            [
                'key'         => 'price_override_threshold',
                'value'       => '20',
                'type'        => 'integer',
                'group'       => 'price',
                'label'       => 'Price override approval threshold (%)',
                'description' => 'Price changes beyond this percentage require owner approval.',
            ],
            [
                'key'         => 'allow_price_override',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'price',
                'label'       => 'Allow sellers to modify prices',
                'description' => 'When disabled, sellers cannot change prices at all.',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
