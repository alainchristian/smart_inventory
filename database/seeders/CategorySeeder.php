<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Footwear',       'code' => 'FOOT',     'description' => 'All footwear products'],
            ['name' => 'Sneakers',       'code' => 'FOOT-SNK', 'description' => null],
            ['name' => 'Dress Shoes',    'code' => 'FOOT-DRS', 'description' => null],
            ['name' => 'Sandals',        'code' => 'FOOT-SND', 'description' => null],
            ['name' => 'School Shoes',   'code' => 'FOOT-SCH', 'description' => null],
            ['name' => 'Sports Shoes',   'code' => 'FOOT-SPT', 'description' => null],
            ['name' => "Ladies' Heels",  'code' => 'FOOT-LDH', 'description' => null],
            ['name' => 'Boots',          'code' => 'FOOT-BOT', 'description' => null],
            ['name' => 'Slippers',       'code' => 'FOOT-SLP', 'description' => null],
            ['name' => 'Accessories',    'code' => 'ACC',      'description' => 'Shoe & fashion accessories'],
            ['name' => 'Socks',          'code' => 'ACC-SOC',  'description' => null],
            ['name' => 'Belts',          'code' => 'ACC-BLT',  'description' => null],
            ['name' => 'Bags',           'code' => 'ACC-BAG',  'description' => null],
            ['name' => 'Shoe Care',      'code' => 'ACC-CRE',  'description' => null],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insertOrIgnore([
                'name'        => $cat['name'],
                'code'        => $cat['code'],
                'description' => $cat['description'],
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $this->command->info('Categories seeded: ' . count($categories));
    }
}
