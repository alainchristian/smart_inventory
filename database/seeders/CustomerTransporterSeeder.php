<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\Transporter;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Sample customers and transporters for the Kigalifootwear demo data.
 *
 * Idempotent: customers are keyed by phone, transporters by vehicle plate,
 * so re-running never duplicates rows.
 *
 * Customer credit figures are deliberately left at zero — balances must come
 * from real credit sales / repayments, otherwise the Customer Credit report
 * no longer reconciles against sales.
 *
 *   php artisan db:seed --class=CustomerTransporterSeeder
 */
class CustomerTransporterSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('role', UserRole::OWNER)->first();
        if (! $owner) {
            $this->command->warn('No owner user found — run BootstrapSeeder first.');
            return;
        }

        $shops = Shop::orderBy('id')->get()->keyBy(fn ($s) => str($s->name)->afterLast('—')->trim()->lower()->toString());

        // Shop manager for each shop registers that shop's customers; owner registers the unassigned ones
        $registrar = fn (?Shop $shop) => $shop
            ? (User::where('role', UserRole::SHOP_MANAGER)->where('location_id', $shop->id)->value('id') ?? $owner->id)
            : $owner->id;

        $customers = [
            // [name, phone, email, shop key|null, notes]
            ['Innocent Mugenzi',      '0788310421', 'innocent.mugenzi@gmail.com', 'remera',     'Buys school shoes in bulk every term'],
            ['Aline Uwase',           '0788452190', null,                         'remera',     null],
            ['Eric Niyonzima',        '0783127764', null,                         'remera',     'Prefers MoMo payment'],
            ['Grace Mukeshimana',     '0722984105', 'grace.muke@yahoo.com',       'remera',     null],
            ['Patrick Habimana',      '0788671230', null,                         'remera',     'Runs a shop in Kabuga — resells'],
            ['Diane Ingabire',        '0785203348', null,                         'nyamirambo', null],
            ['Jean Bosco Nkurunziza', '0788904417', null,                         'nyamirambo', 'Wholesale buyer, usually full boxes'],
            ['Claudine Umutoni',      '0726118930', 'claudine.u@gmail.com',       'nyamirambo', null],
            ['Olivier Tuyishime',     '0789345521', null,                         'nyamirambo', null],
            ['Solange Nyirahabimana', '0784770312', null,                         'kimironko',  null],
            ['Emmanuel Hakizimana',   '0788229064', 'e.hakizimana@outlook.com',   'kimironko',  'Church uniform orders'],
            ['Chantal Murekatete',    '0723556871', null,                         'kimironko',  null],
            ['Fabrice Ishimwe',       '0787019983', null,                         'kimironko',  null],
            ['Vestine Mukamana',      '0788142275', null,                         null,         'Buys from any branch'],
            ['Kigali Parents School', '0788500600', 'procurement@kps.rw',         null,         'Institutional buyer — invoice on delivery'],
            ['Samuel Twagirayezu',    '0782663019', null,                         null,         null],
        ];

        $created = 0;
        foreach ($customers as [$name, $phone, $email, $shopKey, $notes]) {
            $shop = $shopKey ? $shops->get($shopKey) : null;

            $customer = Customer::firstOrCreate(
                ['phone' => $phone],
                [
                    'name'                => $name,
                    'email'               => $email,
                    'notes'               => $notes,
                    'shop_id'             => $shop?->id,
                    'registered_by'       => $registrar($shop),
                    'total_credit_given'  => 0,
                    'total_repaid'        => 0,
                    'outstanding_balance' => 0,
                ]
            );
            $created += $customer->wasRecentlyCreated ? 1 : 0;
        }
        $this->command->info("  Customers: {$created} created, " . (count($customers) - $created) . ' already existed');

        $transporters = [
            // [name, company, phone, plate, licence, active, notes]
            ['Jean Claude Nsengiyumva', 'Rwanda Express Logistics', '0788123987', 'RAD 482 C', 'DL-RW-204118', true,  'Box truck, 3.5 t — main Gisozi → shops route'],
            ['Theoneste Bizimana',      'Rwanda Express Logistics', '0788665120', 'RAE 117 B', 'DL-RW-311902', true,  null],
            ['Alexis Ndayisaba',        'Kigali Moto Cargo',        '0785441276', 'RF 903 K',  'DL-RW-118445', true,  'Motorcycle — small urgent transfers only'],
            ['Faustin Rukundo',         null,                       '0783908812', 'RAC 655 A', 'DL-RW-097310', true,  'Independent pickup driver'],
            ['Gilbert Mutabazi',        'Nyabugogo Transport Co.',  '0788770054', 'RAB 290 F', 'DL-RW-256730', true,  null],
            ['Damascene Uwimana',       null,                       '0722340918', 'RAA 841 D', 'DL-RW-044287', false, 'Inactive — vehicle sold'],
        ];

        $created = 0;
        foreach ($transporters as [$name, $company, $phone, $plate, $licence, $active, $notes]) {
            $transporter = Transporter::firstOrCreate(
                ['vehicle_number' => $plate],
                [
                    'name'           => $name,
                    'company_name'   => $company,
                    'phone'          => $phone,
                    'license_number' => $licence,
                    'is_active'      => $active,
                    'notes'          => $notes,
                ]
            );
            $created += $transporter->wasRecentlyCreated ? 1 : 0;
        }
        $this->command->info("  Transporters: {$created} created, " . (count($transporters) - $created) . ' already existed');
    }
}
