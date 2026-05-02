<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Footwear',       'description' => 'All footwear products'],
            ['name' => 'Sneakers',       'description' => null],
            ['name' => 'Dress Shoes',    'description' => null],
            ['name' => 'Sandals',        'description' => null],
            ['name' => 'School Shoes',   'description' => null],
            ['name' => 'Sports Shoes',   'description' => null],
            ['name' => "Ladies' Heels",  'description' => null],
            ['name' => 'Boots',          'description' => null],
            ['name' => 'Slippers',       'description' => null],
            ['name' => 'Accessories',    'description' => 'Shoe & fashion accessories'],
            ['name' => 'Socks',          'description' => null],
            ['name' => 'Belts',          'description' => null],
            ['name' => 'Bags',           'description' => null],
            ['name' => 'Shoe Care',      'description' => null],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insertOrIgnore([
                'name'        => $cat['name'],
                'description' => $cat['description'],
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $this->command->info('Categories seeded: ' . count($categories));
    }
}
