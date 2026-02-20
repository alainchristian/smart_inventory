#!/bin/bash

set -e

echo "Creating Laravel directories..."

cd /var/www/smart-inventory

# Create storage directories
mkdir -p storage/logs
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "✓ Directories created and permissions set"
