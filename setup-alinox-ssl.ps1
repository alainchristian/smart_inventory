# Setup SSL Certificate for alinox.net
# This script automates the SSL certificate installation for alinox.net domain

$ErrorActionPreference = "Stop"

$DOMAIN = "alinox.net"
$SERVER_IP = "5.78.135.79"
$SERVER_USER = "root"
$SSH_KEY = "$env:USERPROFILE\.ssh\id_ed25519"
$DEPLOY_DIR = "/var/www/smart-inventory"

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  Installing SSL for $DOMAIN" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check DNS
Write-Host "[1/4] Checking DNS configuration..." -ForegroundColor Yellow

try {
    $dnsResult = Resolve-DnsName -Name $DOMAIN -Type A -ErrorAction Stop
    $resolvedIP = $dnsResult | Where-Object { $_.Type -eq "A" } | Select-Object -First 1 -ExpandProperty IPAddress

    if ($resolvedIP -eq $SERVER_IP) {
        Write-Host "DNS is correctly configured!" -ForegroundColor Green
        Write-Host "$DOMAIN -> $resolvedIP`n" -ForegroundColor Gray
    } else {
        Write-Host "WARNING: DNS points to $resolvedIP but should be $SERVER_IP" -ForegroundColor Red
        Write-Host "Please update your DNS A record and wait 10-15 minutes for propagation." -ForegroundColor Yellow
        Write-Host ""
        $continue = Read-Host "Continue anyway? (yes/no)"
        if ($continue -ne "yes") {
            exit 0
        }
    }
} catch {
    Write-Host "Could not resolve DNS for $DOMAIN" -ForegroundColor Red
    Write-Host "Please ensure DNS A record points to $SERVER_IP" -ForegroundColor Yellow
    Write-Host ""
    $continue = Read-Host "Continue anyway? (yes/no)"
    if ($continue -ne "yes") {
        exit 0
    }
}

Write-Host ""

# Step 2: Run the SSL installation script on the server
Write-Host "[2/4] Installing SSL on server..." -ForegroundColor Yellow
Write-Host "This may take 1-2 minutes...`n" -ForegroundColor Gray

$sslInstallScript = @'
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
echo ""
echo "Test URLs:"
echo "  Main app: https://$DOMAIN"
echo "  Scanner:  https://$DOMAIN/scanner"
echo "  Login:    https://$DOMAIN/login"
echo ""
'@

# Execute the SSL installation script on the server
ssh -i $SSH_KEY "$SERVER_USER@$SERVER_IP" $sslInstallScript

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "SSL INSTALLATION FAILED!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Troubleshooting:" -ForegroundColor Yellow
    Write-Host "  - DNS not fully propagated yet (wait 10-15 minutes)" -ForegroundColor Gray
    Write-Host "  - Port 80/443 blocked by firewall" -ForegroundColor Gray
    Write-Host "  - Let's Encrypt rate limit reached (5 per week)" -ForegroundColor Gray
    Write-Host ""
    Write-Host "To check certbot logs:" -ForegroundColor Yellow
    Write-Host "  ssh -i $SSH_KEY $SERVER_USER@$SERVER_IP 'cat /var/log/letsencrypt/letsencrypt.log'" -ForegroundColor Gray
    Write-Host ""
    exit 1
}

Write-Host ""

# Step 3: Verify SSL Certificate
Write-Host "[3/4] Verifying SSL certificate..." -ForegroundColor Yellow

$certInfo = ssh -i $SSH_KEY "$SERVER_USER@$SERVER_IP" "certbot certificates"
Write-Host $certInfo -ForegroundColor Gray
Write-Host ""

# Step 4: Test HTTPS connection
Write-Host "[4/4] Testing HTTPS connection..." -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "https://$DOMAIN" -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
    Write-Host "HTTPS is working! Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Could not verify HTTPS immediately (may need a moment to propagate)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "  SSL INSTALLATION COMPLETE!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Your application is now available at:" -ForegroundColor Cyan
Write-Host "  https://$DOMAIN" -ForegroundColor Cyan
Write-Host "  https://www.$DOMAIN" -ForegroundColor Cyan
Write-Host ""
Write-Host "Features enabled:" -ForegroundColor Yellow
Write-Host "  - Free SSL certificate from Let's Encrypt" -ForegroundColor Gray
Write-Host "  - Automatic HTTP -> HTTPS redirect" -ForegroundColor Gray
Write-Host "  - Auto-renewal every 90 days" -ForegroundColor Gray
Write-Host "  - Both www and non-www work" -ForegroundColor Gray
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "  1. Visit https://$DOMAIN in your browser" -ForegroundColor Gray
Write-Host "  2. Test scanner at https://$DOMAIN/scanner" -ForegroundColor Gray
Write-Host "  3. Update deploy.ps1 to use new domain" -ForegroundColor Gray
Write-Host "  4. Clear DNS cache on mobile devices if needed" -ForegroundColor Gray
Write-Host ""
Write-Host "Security checklist:" -ForegroundColor Cyan
Write-Host "  - Green padlock should appear in browser" -ForegroundColor Gray
Write-Host "  - HTTP automatically redirects to HTTPS" -ForegroundColor Gray
Write-Host "  - Certificate valid for 90 days" -ForegroundColor Gray
Write-Host "  - Auto-renewal timer is active" -ForegroundColor Gray
Write-Host ""
