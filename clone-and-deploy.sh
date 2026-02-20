#!/bin/bash

set -e

echo "========================================="
echo "CLONING AND DEPLOYING SMART INVENTORY"
echo "========================================="
echo ""

# Step 1: Clone the repository
echo "[1/3] Cloning Laravel project from GitHub..."
mkdir -p /var/www
cd /var/www

if [ -d "smart-inventory" ]; then
    echo "  Directory exists, removing old version..."
    rm -rf smart-inventory
fi

git clone https://github.com/alainchristian/smart_inventory.git smart-inventory
cd smart-inventory

if [ -f "artisan" ]; then
    echo "✓ Laravel project cloned successfully"
else
    echo "✗ Clone failed - artisan file not found"
    exit 1
fi

# Step 2: Run complete server setup
echo ""
echo "[2/3] Running server configuration..."
if [ -f "/root/complete-server-setup.sh" ]; then
    bash /root/complete-server-setup.sh
    echo "✓ Server configuration completed"
else
    echo "  complete-server-setup.sh not found, skipping..."
fi

# Step 3: Run deployment script
echo ""
echo "[3/3] Deploying Laravel application..."
if [ -f "/root/deploy-laravel.sh" ]; then
    # Modify deploy script to auto-answer yes for migrations
    sed -i 's/read -p/echo "Auto-answering yes..." # read -p/g' /root/deploy-laravel.sh
    export REPLY="y"
    bash /root/deploy-laravel.sh
    echo "✓ Application deployed"
else
    echo "  deploy-laravel.sh not found, running manual deployment..."

    # Manual deployment steps
    cd /var/www/smart-inventory

    # Install dependencies
    composer install --optimize-autoloader --no-dev --no-interaction

    # Setup environment
    if [ ! -f ".env" ]; then
        cp .env.example .env 2>/dev/null || cat > .env << 'ENVFILE'
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

    # Generate key
    php artisan key:generate --force

    # Setup directories
    mkdir -p storage/framework/{sessions,views,cache}
    mkdir -p storage/logs
    mkdir -p bootstrap/cache

    # Set permissions
    chown -R www-data:www-data /var/www/smart-inventory
    chmod -R 755 /var/www/smart-inventory
    chmod -R 775 /var/www/smart-inventory/storage
    chmod -R 775 /var/www/smart-inventory/bootstrap/cache

    # Build assets
    if [ -f "package.json" ]; then
        npm install --no-audit
        npm run build
    fi

    # Database migrations
    php artisan migrate --force

    # Storage link
    php artisan storage:link

    # Optimize
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Restart services
    systemctl restart php8.3-fpm
    systemctl restart nginx
    supervisorctl reread
    supervisorctl update
    supervisorctl restart all

    echo "✓ Manual deployment completed"
fi

echo ""
echo "========================================="
echo "✅ DEPLOYMENT COMPLETE!"
echo "========================================="
echo ""
echo "🌐 Application URL: http://5.78.135.79"
echo ""
echo "📝 Test the application:"
echo "  curl http://5.78.135.79"
echo ""
