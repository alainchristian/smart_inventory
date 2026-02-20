#!/bin/bash

echo "========================================="
echo "SMART INVENTORY SERVER STATUS CHECK"
echo "========================================="
echo ""

echo "=== SERVICES STATUS ==="
echo "Nginx: $(systemctl is-active nginx)"
echo "PHP 8.3-FPM: $(systemctl is-active php8.3-fpm)"
echo "PostgreSQL: $(systemctl is-active postgresql)"
echo "Redis: $(systemctl is-active redis-server)"
echo "Supervisor: $(systemctl is-active supervisor)"
echo ""

echo "=== SOFTWARE VERSIONS ==="
echo "PHP: $(php -v | head -1 | cut -d' ' -f2)"
echo "Nginx: $(nginx -v 2>&1 | cut -d'/' -f2)"
echo "PostgreSQL: $(sudo -u postgres psql --version | cut -d' ' -f3)"
echo "Node.js: $(node -v)"
echo "Composer: $(composer --version --no-ansi | head -1 | cut -d' ' -f3)"
echo ""

echo "=== LARAVEL PROJECT STATUS ==="
if [ -f "/var/www/smart-inventory/artisan" ]; then
    echo "✓ Laravel project EXISTS at /var/www/smart-inventory"
    echo "  Owner: $(stat -c '%U:%G' /var/www/smart-inventory)"
    echo "  .env exists: $([ -f /var/www/smart-inventory/.env ] && echo YES || echo NO)"
    echo "  vendor/ exists: $([ -d /var/www/smart-inventory/vendor ] && echo YES || echo NO)"
    echo "  public/ exists: $([ -d /var/www/smart-inventory/public ] && echo YES || echo NO)"
else
    echo "✗ Laravel project NOT FOUND at /var/www/smart-inventory"
fi
echo ""

echo "=== DATABASE STATUS ==="
sudo -u postgres psql -l | grep smart_inventory || echo "Database 'smart_inventory' NOT found"
echo ""

echo "=== NGINX CONFIGURATION ==="
if [ -f "/etc/nginx/sites-available/smart-inventory" ]; then
    echo "✓ Nginx config EXISTS"
    echo "  Enabled: $([ -L /etc/nginx/sites-enabled/smart-inventory ] && echo YES || echo NO)"
else
    echo "✗ Nginx config NOT found"
fi
echo ""

echo "=== SUPERVISOR WORKERS ==="
supervisorctl status 2>&1 || echo "No workers configured"
echo ""

echo "=== DEPLOYMENT INFO ==="
if [ -f "/root/deployment-info.txt" ]; then
    cat /root/deployment-info.txt
else
    echo "No deployment-info.txt file found"
fi
