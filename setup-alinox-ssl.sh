#!/bin/bash
set -e

DOMAIN="alinox.net"
APP_DIR="/var/www/smart-inventory"

echo "================================================"
echo "  Installing SSL for $DOMAIN"
echo "================================================"
echo ""

# Step 1: Stop Apache if running (might conflict with Nginx)
echo "[1/8] Checking for Apache..."
systemctl stop apache2 2>/dev/null || true
systemctl disable apache2 2>/dev/null || true
apt remove apache2 -y 2>/dev/null || true
echo "Apache removed/disabled"
echo ""

# Step 2: Update Nginx configuration for alinox.net
echo "[2/8] Configuring Nginx..."
cat > /etc/nginx/sites-available/smart-inventory << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name alinox.net www.alinox.net;
    root /var/www/smart-inventory/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable the site
ln -sf /etc/nginx/sites-available/smart-inventory /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

echo "Nginx configured for $DOMAIN"
echo ""

# Step 3: Test Nginx configuration
echo "[3/8] Testing Nginx configuration..."
nginx -t
echo ""

# Step 4: Reload Nginx
echo "[4/8] Reloading Nginx..."
systemctl reload nginx
echo "Nginx reloaded"
echo ""

# Step 5: Install Certbot if not present
echo "[5/8] Installing Certbot..."
apt update -qq
apt install -y certbot python3-certbot-nginx
echo "Certbot installed"
echo ""

# Step 6: Get SSL certificate from Let's Encrypt
echo "[6/8] Obtaining SSL certificate from Let's Encrypt..."
echo "This may take 1-2 minutes..."
certbot --nginx \
    -d $DOMAIN \
    -d www.$DOMAIN \
    --non-interactive \
    --agree-tos \
    --email admin@$DOMAIN \
    --redirect

echo ""
echo "SSL certificate obtained and installed!"
echo ""

# Step 7: Update Laravel configuration
echo "[7/8] Updating Laravel configuration..."
cd $APP_DIR

# Update .env file with HTTPS URL
sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|g" .env

# Clear all Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "Laravel configured for HTTPS"
echo ""

# Step 8: Restart services
echo "[8/8] Restarting services..."
systemctl restart php8.3-fpm
systemctl restart nginx

# Enable certbot auto-renewal timer
systemctl enable certbot.timer
systemctl start certbot.timer

echo "Services restarted"
echo ""

# Verify SSL certificate
echo "================================================"
echo "  SSL Installation Complete!"
echo "================================================"
echo ""
echo "Certificate details:"
certbot certificates | grep -A 5 "Certificate Name: $DOMAIN" || certbot certificates | head -20
echo ""
echo "Your application is now available at:"
echo "  https://$DOMAIN"
echo "  https://www.$DOMAIN"
echo ""
echo "SSL certificate will automatically renew every 90 days"
echo "Next renewal check: $(systemctl show certbot.timer | grep NextElapseUSecRealtime | cut -d= -f2)"
echo ""
echo "Test URLs:"
echo "  Main app: https://$DOMAIN"
echo "  Scanner:  https://$DOMAIN/scanner"
echo "  Login:    https://$DOMAIN/login"
echo ""
