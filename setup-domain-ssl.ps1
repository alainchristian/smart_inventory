# Setup alain.rw with Let's Encrypt SSL
# This script automates the domain and SSL certificate setup

$ErrorActionPreference = "Stop"

$DOMAIN = "alain.rw"
$SERVER_IP = "5.78.135.79"
$SERVER_USER = "root"
$SSH_KEY = "$env:USERPROFILE\.ssh\id_ed25519"
$DEPLOY_DIR = "/var/www/smart-inventory"

Write-Host ""
Write-Host "=== Setting Up $DOMAIN with SSL ===" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check DNS
Write-Host "[1/4] Checking DNS configuration..." -ForegroundColor Yellow

try {
    $dnsResult = Resolve-DnsName -Name $DOMAIN -Type A -ErrorAction Stop
    $resolvedIP = $dnsResult | Where-Object { $_.Type -eq "A" } | Select-Object -First 1 -ExpandProperty IPAddress

    if ($resolvedIP -eq $SERVER_IP) {
        Write-Host "DNS is correctly configured!" -ForegroundColor Green
        Write-Host "$DOMAIN → $resolvedIP`n" -ForegroundColor Gray
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

# Step 2: Configure Nginx
Write-Host "[2/4] Configuring Nginx for $DOMAIN..." -ForegroundColor Yellow

$nginxConfig = @"
# Stop Apache if running
systemctl stop apache2 2>/dev/null || true
systemctl disable apache2 2>/dev/null || true

# Create Nginx configuration
cat > /etc/nginx/sites-available/smart-inventory << 'NGINX_EOF'
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;
    root $DEPLOY_DIR/public;

    add_header X-Frame-Options \"SAMEORIGIN\";
    add_header X-Content-Type-Options \"nosniff\";

    index index.php;
    charset utf-8;

    location / {
        try_files \`$uri \`$uri/ /index.php?\`$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\`$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \`$realpath_root\`$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX_EOF

# Enable the site
ln -sf /etc/nginx/sites-available/smart-inventory /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test and reload Nginx
nginx -t && systemctl reload nginx

echo 'Nginx configured successfully'
"@

ssh -i $SSH_KEY "$SERVER_USER@$SERVER_IP" $nginxConfig

if ($LASTEXITCODE -ne 0) {
    Write-Host "Failed to configure Nginx!" -ForegroundColor Red
    exit 1
}

Write-Host "Nginx configured for $DOMAIN`n" -ForegroundColor Green

# Step 3: Install SSL Certificate
Write-Host "[3/4] Installing Let's Encrypt SSL certificate..." -ForegroundColor Yellow
Write-Host "This may take 1-2 minutes...`n" -ForegroundColor Gray

$sslSetup = @"
# Install certbot if needed
apt update > /dev/null 2>&1
apt install -y certbot python3-certbot-nginx > /dev/null 2>&1

# Get SSL certificate
certbot --nginx -d $DOMAIN -d www.$DOMAIN \
    --non-interactive \
    --agree-tos \
    --email admin@$DOMAIN \
    --redirect

# Enable auto-renewal
systemctl enable certbot.timer > /dev/null 2>&1
systemctl start certbot.timer > /dev/null 2>&1

echo 'SSL certificate installed'
"@

ssh -i $SSH_KEY "$SERVER_USER@$SERVER_IP" $sslSetup

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "SSL certificate installation failed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Common causes:" -ForegroundColor Yellow
    Write-Host "  - DNS not fully propagated yet (wait 10-15 minutes)" -ForegroundColor Gray
    Write-Host "  - Port 80/443 blocked by firewall" -ForegroundColor Gray
    Write-Host "  - Let's Encrypt rate limit reached (5 per week)" -ForegroundColor Gray
    Write-Host ""
    exit 1
}

Write-Host "SSL certificate installed!`n" -ForegroundColor Green

# Step 4: Update Laravel Configuration
Write-Host "[4/4] Updating Laravel configuration..." -ForegroundColor Yellow

$laravelUpdate = @"
cd $DEPLOY_DIR

# Update .env
sed -i 's|APP_URL=.*|APP_URL=https://$DOMAIN|g' .env

# Clear and cache config
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
php artisan config:cache > /dev/null 2>&1

# Restart services
systemctl restart php8.3-fpm nginx

echo 'Laravel configured for https://$DOMAIN'
"@

ssh -i $SSH_KEY "$SERVER_USER@$SERVER_IP" $laravelUpdate

if ($LASTEXITCODE -ne 0) {
    Write-Host "Failed to update Laravel configuration!" -ForegroundColor Red
    exit 1
}

Write-Host "Laravel configured`n" -ForegroundColor Green

# Verify SSL Certificate
Write-Host "Verifying SSL certificate..." -ForegroundColor Yellow

$certInfo = ssh -i $SSH_KEY "$SERVER_USER@$SERVER_IP" "certbot certificates | grep -A 2 '$DOMAIN'"
Write-Host $certInfo -ForegroundColor Gray

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "  SETUP COMPLETE!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Your application is now available at:" -ForegroundColor Cyan
Write-Host "  https://$DOMAIN" -ForegroundColor Cyan
Write-Host ""
Write-Host "Features enabled:" -ForegroundColor Yellow
Write-Host "  - Free SSL certificate from Let's Encrypt" -ForegroundColor Gray
Write-Host "  - Automatic HTTP → HTTPS redirect" -ForegroundColor Gray
Write-Host "  - Auto-renewal every 90 days" -ForegroundColor Gray
Write-Host "  - www.$DOMAIN also works" -ForegroundColor Gray
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "  1. Visit https://$DOMAIN in your browser" -ForegroundColor Gray
Write-Host "  2. Test phone scanner at https://$DOMAIN/scanner" -ForegroundColor Gray
Write-Host "  3. Use .\deploy.ps1 for future deployments" -ForegroundColor Gray
Write-Host ""

# Test HTTPS endpoint
try {
    Write-Host "Testing HTTPS connection..." -ForegroundColor Yellow
    $response = Invoke-WebRequest -Uri "https://$DOMAIN" -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
    Write-Host "HTTPS is working! Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host ""
} catch {
    Write-Host "Could not verify HTTPS (may need a moment to propagate)" -ForegroundColor Yellow
    Write-Host "Try visiting https://$DOMAIN in your browser" -ForegroundColor Gray
    Write-Host ""
}
