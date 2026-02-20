#!/bin/bash

cd /var/www/smart-inventory

# Log file
LOG="/root/deployment.log"
echo "Starting deployment at $(date)" > $LOG

# Generate app key
echo "[1/8] Generating app key..." | tee -a $LOG
php artisan key:generate --force >> $LOG 2>&1

# Install Composer dependencies
echo "[2/8] Installing composer dependencies..." | tee -a $LOG
export COMPOSER_ALLOW_SUPERUSER=1
composer install --optimize-autoloader --no-dev --no-interaction >> $LOG 2>&1

# Create directories
echo "[3/8] Setting up directories..." | tee -a $LOG
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache

# Set permissions
echo "[4/8] Setting permissions..." | tee -a $LOG
chown -R www-data:www-data /var/www/smart-inventory
chmod -R 755 /var/www/smart-inventory
chmod -R 775 storage bootstrap/cache

# Install npm and build
echo "[5/8] Installing npm dependencies..." | tee -a $LOG
npm install --no-audit >> $LOG 2>&1

echo "[6/8] Building assets..." | tee -a $LOG
npm run build >> $LOG 2>&1

# Database
echo "[7/8] Running migrations..." | tee -a $LOG
php artisan migrate --force >> $LOG 2>&1

# Optimize
echo "[8/8] Optimizing..." | tee -a $LOG
php artisan storage:link --force >> $LOG 2>&1
php artisan config:cache >> $LOG 2>&1
php artisan route:cache >> $LOG 2>&1
php artisan view:cache >> $LOG 2>&1

# Restart services
systemctl restart php8.3-fpm nginx
supervisorctl reread
supervisorctl update

echo "Deployment completed at $(date)" | tee -a $LOG
echo "✅ DEPLOYMENT COMPLETE!" | tee -a $LOG
