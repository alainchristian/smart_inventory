<?php

use App\Models\Transporter;

echo "=== All Transporters ===\n\n";

$transporters = Transporter::all();

if ($transporters->isEmpty()) {
    echo "No transporters found in database.\n";
} else {
    foreach ($transporters as $t) {
        echo "ID: {$t->id}\n";
        echo "Name: {$t->name}\n";
        echo "Company: {$t->company_name}\n";
        echo "Phone: {$t->phone}\n";
        echo "Vehicle: {$t->vehicle_number}\n";
        echo "License: {$t->license_number}\n";
        echo "Active: " . ($t->is_active ? 'YES' : 'NO') . "\n";
        echo "---\n";
    }

    echo "\nTotal: " . $transporters->count() . " transporters\n";
    echo "Active: " . Transporter::active()->count() . " transporters\n";
}
