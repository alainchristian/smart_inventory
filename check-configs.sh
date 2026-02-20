#!/bin/bash

echo "========================================="
echo "CONFIGURATION FILES CHECK"
echo "========================================="
echo ""

echo "=== NGINX CONFIGURATION ==="
if [ -f "/etc/nginx/sites-available/smart-inventory" ]; then
    cat /etc/nginx/sites-available/smart-inventory
else
    echo "File not found"
fi
echo ""
echo ""

echo "=== DATABASE CONNECTION TEST ==="
sudo -u postgres psql -d smart_inventory -c "SELECT current_database(), current_user, version();" 2>&1
echo ""

echo "=== SUPERVISOR CONFIG ==="
if [ -f "/etc/supervisor/conf.d/smart-inventory-worker.conf" ]; then
    cat /etc/supervisor/conf.d/smart-inventory-worker.conf
else
    echo "Supervisor config not found"
fi
echo ""

echo "=== PHP-FPM CONFIG ==="
grep -E "^(pm\.|listen)" /etc/php/8.3/fpm/pool.d/www.conf | head -10
echo ""

echo "=== DIRECTORY STRUCTURE ==="
echo "Checking /var/www/:"
ls -la /var/www/ 2>&1
