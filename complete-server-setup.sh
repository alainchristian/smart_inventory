#!/bin/bash

# Complete Server Configuration Script
# Run this on the server to finish configuration

set -e

echo "========================================="
echo "Completing Server Configuration"
echo "========================================="
echo ""

# Configure Supervisor
echo "[1/3] Configuring Supervisor..."
cat > /etc/supervisor/conf.d/smart-inventory-worker.conf << 'EOFSUP'
[program:smart-inventory-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/smart-inventory/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/smart-inventory/storage/logs/worker.log
stopwaitsecs=3600
EOFSUP

supervisorctl reread
supervisorctl update
echo "✓ Supervisor configured"

# Optimize PHP-FPM
echo "[2/3] Optimizing PHP-FPM..."
sed -i 's/^pm.max_children = .*/pm.max_children = 10/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm.start_servers = .*/pm.start_servers = 4/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm.min_spare_servers = .*/pm.min_spare_servers = 2/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm.max_spare_servers = .*/pm.max_spare_servers = 6/' /etc/php/8.3/fpm/pool.d/www.conf
systemctl restart php8.3-fpm
echo "✓ PHP-FPM optimized"

# Create deployment info
echo "[3/3] Creating deployment info..."
cat > /root/deployment-info.txt << 'EOFINFO'
========================================
SMART INVENTORY - DEPLOYMENT INFO
========================================

DATABASE CREDENTIALS
--------------------
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smart_inventory
DB_USERNAME=inventory_user
DB_PASSWORD=SmartInv@2026!Secure

APPLICATION PATHS
-----------------
Root Directory: /var/www/smart-inventory
Public Directory: /var/www/smart-inventory/public
Owner: www-data:www-data

NGINX CONFIGURATION
-------------------
Server: http://5.78.135.79
Config: /etc/nginx/sites-available/smart-inventory

SERVICES STATUS
---------------
All services configured and running:
- Nginx (Web Server)
- PHP 8.3-FPM (PHP Handler)
- PostgreSQL 16 (Database)
- Redis (Cache/Queue)
- Supervisor (Queue Workers - 2 processes)

NEXT STEPS
----------
1. Deploy your Laravel application to /var/www/smart-inventory
2. Run: bash /root/deploy-laravel.sh

EOFINFO

echo "✓ Deployment info saved"
echo ""
echo "========================================="
echo "✅ Server Configuration Complete!"
echo "========================================="
echo ""
echo "Application URL: http://5.78.135.79"
echo "Credentials saved to: /root/deployment-info.txt"
echo ""
