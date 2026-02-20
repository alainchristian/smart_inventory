#!/bin/bash

echo "=== DEPLOYMENT STATUS ==="
echo ""

if [ -f "/var/www/smart-inventory/artisan" ]; then
    echo "✓ Project cloned successfully"
    echo "  Files: $(ls -1 /var/www/smart-inventory | wc -l) items"
    echo "  Owner: $(stat -c '%U:%G' /var/www/smart-inventory)"
    echo ""

    echo "=== APPLICATION STATUS ==="
    echo ".env exists: $([ -f /var/www/smart-inventory/.env ] && echo YES || echo NO)"
    echo "vendor/ exists: $([ -d /var/www/smart-inventory/vendor ] && echo YES || echo NO)"
    echo "node_modules/ exists: $([ -d /var/www/smart-inventory/node_modules ] && echo YES || echo NO)"
    echo "public/build/ exists: $([ -d /var/www/smart-inventory/public/build ] && echo YES || echo NO)"

    echo ""
    echo "=== LAST 20 LINES OF LOG ==="
    tail -20 /var/www/smart-inventory/storage/logs/laravel.log 2>&1 || echo "No logs yet"
else
    echo "✗ Project NOT found at /var/www/smart-inventory"
    echo ""
    echo "Checking for running processes:"
    ps aux | grep -E "(git|composer|npm)" | grep -v grep || echo "No deployment processes running"
fi

echo ""
echo "=== WEB SERVER TEST ==="
curl -I http://localhost 2>&1 | head -5
