#!/bin/bash

# Laravel Application Deployment Script
# Deploys and configures the Smart Inventory Laravel application

set -e

APP_DIR="/var/www/smart-inventory"
APP_USER="www-data"
APP_GROUP="www-data"

echo "========================================="
echo "Laravel Application Deployment"
echo "========================================="
echo ""

# Check if application exists
if [ ! -f "$APP_DIR/artisan" ]; then
    echo "⚠ Laravel application not found in $APP_DIR"
    echo ""
    echo "Please deploy your application first:"
    echo ""
    echo "Option 1: Clone from Git"
    echo "  cd $APP_DIR"
    echo "  git clone <your-repo-url> ."
    echo ""
    echo "Option 2: Upload via SCP"
    echo "  From your local machine:"
    echo "  scp -r /path/to/smart-inventory/* root@5.78.135.79:$APP_DIR/"
    echo ""
    echo "Then run: bash /root/deploy-laravel.sh"
    exit 1
fi

cd $APP_DIR

echo "[1/9] Checking application files..."
if [ ! -f "composer.json" ]; then
    echo "✗ composer.json not found!"
    exit 1
fi
echo "✓ Application files found"

# Install Composer dependencies
echo "[2/9] Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction
echo "✓ Composer dependencies installed"

# Create .env if needed
echo "[3/9] Configuring environment..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "  Created .env from example"
    else
        cat > .env << 'EOFENV'
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
EOFENV
        echo "  Created minimal .env"
    fi
fi

# Generate app key
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
    echo "  Application key generated"
fi
echo "✓ Environment configured"

# Create directories
echo "[4/9] Setting up storage..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
echo "✓ Storage directories created"

# Set permissions
echo "[5/9] Setting permissions..."
chown -R $APP_USER:$APP_GROUP $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache
echo "✓ Permissions set"

# Build frontend assets
echo "[6/9] Building frontend assets..."
if [ -f "package.json" ]; then
    npm install --no-audit
    npm run build
    echo "✓ Assets built"
else
    echo "  No package.json, skipping"
fi

# Database migrations
echo "[7/9] Database setup..."
read -p "Run migrations? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    echo "✓ Migrations completed"

    read -p "Seed database? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan db:seed --force
        echo "✓ Database seeded"
    fi
else
    echo "  Skipped (run later: php artisan migrate --force)"
fi

# Create storage link
echo "[8/9] Creating storage link..."
php artisan storage:link
echo "✓ Storage link created"

# Optimize Laravel
echo "[9/9] Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Laravel optimized"

# Restart services
echo ""
echo "Restarting services..."
systemctl restart php8.3-fpm
systemctl restart nginx
supervisorctl reread
supervisorctl update
supervisorctl restart all

echo ""
echo "========================================="
echo "✅ Deployment Complete!"
echo "========================================="
echo ""
echo "🌐 Application URL: http://5.78.135.79"
echo ""
echo "📝 Useful Commands:"
echo "  View logs:       tail -f storage/logs/laravel.log"
echo "  Clear cache:     php artisan cache:clear"
echo "  Restart workers: supervisorctl restart all"
echo "  Run migrations:  php artisan migrate"
echo ""
echo "📄 Database info: /root/deployment-info.txt"
echo ""
