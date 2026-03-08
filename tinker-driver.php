<?php

use App\Models\Transporter;

// Create sample drivers/transporters

$drivers = [
    [
        'name' => 'John Doe',
        'company_name' => 'Express Logistics',
        'phone' => '+256700123456',
        'vehicle_number' => 'UAH 123X',
        'license_number' => 'DL123456',
        'notes' => 'Reliable driver, handles fragile items with care',
        'is_active' => true,
    ],
    [
        'name' => 'Jane Smith',
        'company_name' => 'Swift Transport',
        'phone' => '+256700234567',
        'vehicle_number' => 'UAK 456Y',
        'license_number' => 'DL234567',
        'notes' => 'Experienced with long-distance deliveries',
        'is_active' => true,
    ],
    [
        'name' => 'Peter Mukasa',
        'company_name' => 'FastTrack Couriers',
        'phone' => '+256700345678',
        'vehicle_number' => 'UAN 789Z',
        'license_number' => 'DL345678',
        'notes' => 'Specialist in same-day delivery',
        'is_active' => true,
    ],
];

foreach ($drivers as $driverData) {
    $driver = Transporter::create($driverData);
    echo "Created driver: {$driver->name} (ID: {$driver->id})\n";
}

echo "\n✓ Successfully created " . count($drivers) . " drivers/transporters\n";
