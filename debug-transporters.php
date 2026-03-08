<?php

use App\Models\Transporter;

echo "Checking transporters...\n\n";

// Get all transporters
$all = Transporter::all();
echo "Total transporters: " . $all->count() . "\n\n";

// Get active transporters
$active = Transporter::where('is_active', true)->get();
echo "Active transporters: " . $active->count() . "\n\n";

// Show details
if ($all->count() > 0) {
    echo "=== ALL TRANSPORTERS ===\n";
    foreach ($all as $t) {
        echo sprintf(
            "ID: %d | Name: %s | Active: %s | Vehicle: %s\n",
            $t->id,
            $t->name,
            $t->is_active ? 'YES' : 'NO',
            $t->vehicle_number ?? 'N/A'
        );
    }
} else {
    echo "No transporters found! Running tinker-driver.php script...\n\n";

    // Create drivers
    $drivers = [
        [
            'name' => 'John Doe',
            'company_name' => 'Express Logistics',
            'phone' => '+256700123456',
            'vehicle_number' => 'UAH 123X',
            'license_number' => 'DL123456',
            'notes' => 'Reliable driver',
            'is_active' => true,
        ],
        [
            'name' => 'Jane Smith',
            'company_name' => 'Swift Transport',
            'phone' => '+256700234567',
            'vehicle_number' => 'UAK 456Y',
            'license_number' => 'DL234567',
            'notes' => 'Experienced driver',
            'is_active' => true,
        ],
        [
            'name' => 'Peter Mukasa',
            'company_name' => 'FastTrack Couriers',
            'phone' => '+256700345678',
            'vehicle_number' => 'UAN 789Z',
            'license_number' => 'DL345678',
            'notes' => 'Same-day delivery specialist',
            'is_active' => true,
        ],
    ];

    foreach ($drivers as $driverData) {
        $driver = Transporter::create($driverData);
        echo "Created: {$driver->name} (ID: {$driver->id})\n";
    }

    echo "\nTransporters created successfully!\n";
}
