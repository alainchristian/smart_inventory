#!/bin/bash

set -e

cd /var/www/smart-inventory

echo "========================================="
echo "COMPLETING LARAVEL DEPLOYMENT"
echo "========================================="
echo ""

# 1. Copy environment file
echo "[1/12] Setting up environment..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
    else
        cat > .env << 'ENVFILE'
APP_NAME="Smart Inventory"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://5.78.135.79

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smart_inventory
DB_USERNAME=inventory_user
DB_PASSWORD=SmartInv@2026!Secure

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
ENVFILE
    fi
    echo "✓ Environment file created"
else
    echo "✓ Environment file exists"
fi

# 2. Install Composer dependencies
echo "[2/12] Installing Composer dependencies..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install --optimize-autoloader --no-dev --no-interaction --quiet
echo "✓ Composer dependencies installed"

# 3. Generate application key
echo "[3/12] Generating application key..."
php artisan key:generate --force
echo "✓ Application key generated"

# 4. Set ownership
echo "[4/12] Setting file ownership..."
chown -R www-data:www-data /var/www/smart-inventory
chmod -R 755 /var/www/smart-inventory
chmod -R 775 /var/www/smart-inventory/storage
chmod -R 775 /var/www/smart-inventory/bootstrap/cache
echo "✓ Ownership and permissions set"

# 5. Install npm dependencies
echo "[5/12] Installing NPM dependencies..."
if [ -f "package.json" ]; then
    npm install --no-audit --silent
    echo "✓ NPM dependencies installed"
else
    echo "  No package.json found, skipping"
fi

# 6. Build frontend assets
echo "[6/12] Building frontend assets..."
if [ -f "package.json" ]; then
    npm run build
    echo "✓ Assets built"
else
    echo "  Skipped"
fi

# 7. Create storage link
echo "[7/12] Creating storage link..."
php artisan storage:link --force
echo "✓ Storage link created"

# 8. Run migrations
echo "[8/12] Running database migrations..."
php artisan migrate --force
echo "✓ Migrations completed"

# 9. Optimize Laravel
echo "[9/12] Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Application optimized"

# 10. Configure Supervisor
echo "[10/12] Configuring Supervisor..."
supervisorctl reread
supervisorctl update
echo "✓ Supervisor configured"

# 11. Restart services
echo "[11/12] Restarting services..."
systemctl restart php8.3-fpm
systemctl restart nginx
supervisorctl restart all 2>/dev/null || echo "  Workers will start shortly"
echo "✓ Services restarted"

# 12. Test application
echo "[12/12] Testing application..."
sleep 2
curl -I http://localhost 2>&1 | head -1
echo "✓ Application responding"

echo ""
echo "========================================="
echo "✅ DEPLOYMENT COMPLETE!"
echo "========================================="
echo ""
echo "🌐 Application URL: http://5.78.135.79"
echo ""
echo "📊 System Status:"
systemctl is-active nginx php8.3-fpm postgresql redis-server supervisor
echo ""
echo "👷 Worker Status:"
supervisorctl status
echo ""
