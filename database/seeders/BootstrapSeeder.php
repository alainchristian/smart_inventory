<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🚀  SmartInventory — Bootstrap Seed');
        $this->command->info('────────────────────────────────────────');

        // ── Users ──────────────────────────────────────
        $owner = User::create([
            'name'          => 'Jean-Pierre Habimana',
            'email'         => 'owner@kigalifootwear.rw',
            'password'      => Hash::make('password'),
            'role'          => 'owner',
            'phone'         => '+250788100001',
            'location_type' => null,
            'location_id'   => null,
            'is_active'     => true,
        ]);

        $wm1 = User::create([
            'name'      => 'Emmanuel Nzeyimana',
            'email'     => 'wm1@kigalifootwear.rw',
            'password'  => Hash::make('password'),
            'role'      => 'warehouse_manager',
            'phone'     => '+250788200001',
            'is_active' => true,
        ]);

        $wm2 = User::create([
            'name'      => 'Celestine Mukamana',
            'email'     => 'wm2@kigalifootwear.rw',
            'password'  => Hash::make('password'),
            'role'      => 'warehouse_manager',
            'phone'     => '+250788200002',
            'is_active' => true,
        ]);

        $sm1 = User::create([
            'name'      => 'Alice Uwimana',
            'email'     => 'shop1@kigalifootwear.rw',
            'password'  => Hash::make('password'),
            'role'      => 'shop_manager',
            'phone'     => '+250788300001',
            'is_active' => true,
        ]);

        $sm2 = User::create([
            'name'      => 'Robert Kayitare',
            'email'     => 'shop2@kigalifootwear.rw',
            'password'  => Hash::make('password'),
            'role'      => 'shop_manager',
            'phone'     => '+250788300002',
            'is_active' => true,
        ]);

        $sm3 = User::create([
            'name'      => 'Marie-Claire Ingabire',
            'email'     => 'shop3@kigalifootwear.rw',
            'password'  => Hash::make('password'),
            'role'      => 'shop_manager',
            'phone'     => '+250788300003',
            'is_active' => true,
        ]);

        // ── Warehouse ──────────────────────────────────
        $warehouse = Warehouse::create([
            'name'         => 'Kigali Central Warehouse',
            'code'         => 'WH-KGL-01',
            'address'      => 'KG 11 Ave, Gisozi Industrial Zone',
            'city'         => 'Kigali',
            'phone'        => '+250788400001',
            'manager_name' => $wm1->name,
            'is_active'    => true,
        ]);

        $wm1->update(['location_type' => 'warehouse', 'location_id' => $warehouse->id]);
        $wm2->update(['location_type' => 'warehouse', 'location_id' => $warehouse->id]);

        // ── Shops ──────────────────────────────────────
        $shop1 = Shop::create([
            'name'                 => 'Kigalifootwear — Remera',
            'code'                 => 'SHOP-REM',
            'address'              => 'KG 9 Ave, Remera',
            'city'                 => 'Kigali',
            'phone'                => '+250788500001',
            'manager_name'         => $sm1->name,
            'default_warehouse_id' => $warehouse->id,
            'is_active'            => true,
        ]);

        $shop2 = Shop::create([
            'name'                 => 'Kigalifootwear — Nyamirambo',
            'code'                 => 'SHOP-NYM',
            'address'              => 'KN 3 Rd, Nyamirambo',
            'city'                 => 'Kigali',
            'phone'                => '+250788500002',
            'manager_name'         => $sm2->name,
            'default_warehouse_id' => $warehouse->id,
            'is_active'            => true,
        ]);

        $shop3 = Shop::create([
            'name'                 => 'Kigalifootwear — Kimironko',
            'code'                 => 'SHOP-KIM',
            'address'              => 'KG 7 Ave, Kimironko Market',
            'city'                 => 'Kigali',
            'phone'                => '+250788500003',
            'manager_name'         => $sm3->name,
            'default_warehouse_id' => $warehouse->id,
            'is_active'            => true,
        ]);

        $sm1->update(['location_type' => 'shop', 'location_id' => $shop1->id]);
        $sm2->update(['location_type' => 'shop', 'location_id' => $shop2->id]);
        $sm3->update(['location_type' => 'shop', 'location_id' => $shop3->id]);

        $this->command->info('');
        $this->command->info('  ✅  Bootstrap complete!');
        $this->command->info('');
        $this->command->info('  👥  LOGINS (all: password)');
        $this->command->info('      owner@kigalifootwear.rw     — Owner');
        $this->command->info('      wm1@kigalifootwear.rw       — Warehouse Manager (Gisozi)');
        $this->command->info('      wm2@kigalifootwear.rw       — Warehouse Manager (Gisozi)');
        $this->command->info('      shop1@kigalifootwear.rw     — Shop Manager (Remera)');
        $this->command->info('      shop2@kigalifootwear.rw     — Shop Manager (Nyamirambo)');
        $this->command->info('      shop3@kigalifootwear.rw     — Shop Manager (Kimironko)');
        $this->command->info('');
        $this->command->info('  📦  No products yet.');
        $this->command->info('      Log in as wm1@ and use Receive Boxes → Excel upload');
        $this->command->info('      to create products and stock in one step.');
        $this->command->info('');
    }
}