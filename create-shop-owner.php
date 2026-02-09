<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Shop;
use App\Enums\UserRole;
use App\Enums\LocationType;

echo "Checking existing users:\n";
$users = User::all(['id', 'name', 'email', 'role']);
foreach ($users as $user) {
    echo "ID: {$user->id} - {$user->name} ({$user->email}) - Role: {$user->role->value}\n";
}

echo "\n---\n\n";

// Get first shop
$shop = Shop::first();
if (!$shop) {
    echo "ERROR: No shops found in database. Please create a shop first.\n";
    exit(1);
}

echo "Found shop: {$shop->name} (ID: {$shop->id})\n\n";

// Check if shop owner already exists for this shop
$existingOwner = User::where('role', UserRole::SHOP_OWNER)
    ->where('location_type', LocationType::SHOP)
    ->where('location_id', $shop->id)
    ->first();

if ($existingOwner) {
    echo "Shop owner already exists:\n";
    echo "Name: {$existingOwner->name}\n";
    echo "Email: {$existingOwner->email}\n";
    echo "Shop: {$shop->name}\n";
} else {
    echo "Creating new shop owner...\n";

    $shopOwner = User::create([
        'name' => 'Test Shop Owner',
        'email' => 'shopowner@test.com',
        'password' => bcrypt('password'),
        'role' => UserRole::SHOP_OWNER,
        'location_type' => LocationType::SHOP,
        'location_id' => $shop->id,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    echo "✓ Shop owner created successfully!\n";
    echo "Email: shopowner@test.com\n";
    echo "Password: password\n";
    echo "Shop: {$shop->name}\n";
}

echo "\n---\n\n";
echo "You can now:\n";
echo "1. Clear your browser cache\n";
echo "2. Login with the shop owner credentials\n";
echo "3. You should see the purple 'Shop Owner' badge and combined menu\n";
