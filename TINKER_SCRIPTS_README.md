# Tinker Scripts

This directory contains tinker scripts for quickly creating test data in the database.

## Available Scripts

### tinker-driver.php
Creates sample drivers/transporters in the system.

**Usage:**
```bash
php artisan tinker < tinker-driver.php
```

**What it creates:**
- 3 sample drivers with complete information
- Each driver includes: name, company, phone, vehicle number, license number, and notes
- All drivers are set as active by default

**Sample drivers:**
1. John Doe (Express Logistics) - UAH 123X
2. Jane Smith (Swift Transport) - UAK 456Y
3. Peter Mukasa (FastTrack Couriers) - UAN 789Z

## Creating Individual Drivers

You can also create individual drivers manually in tinker:

```bash
php artisan tinker
```

Then run:

```php
use App\Models\Transporter;

$driver = Transporter::create([
    'name' => 'Driver Name',
    'company_name' => 'Company Name',
    'phone' => '+256700000000',
    'vehicle_number' => 'UAX 000X',
    'license_number' => 'DL000000',
    'notes' => 'Optional notes about the driver',
    'is_active' => true,
]);
```

## Viewing All Drivers

```bash
php artisan tinker
```

```php
use App\Models\Transporter;

// Get all drivers
Transporter::all();

// Get only active drivers
Transporter::active()->get();

// Count drivers
Transporter::count();
```

## Updating a Driver

```php
use App\Models\Transporter;

$driver = Transporter::find(1);
$driver->update([
    'phone' => '+256700999999',
    'vehicle_number' => 'NEW 123X',
]);
```

## Deactivating a Driver

```php
use App\Models\Transporter;

$driver = Transporter::find(1);
$driver->update(['is_active' => false]);
```
