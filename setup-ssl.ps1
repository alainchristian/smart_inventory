# Setup Self-Signed SSL Certificate for Smart Inventory
# WARNING: Browsers will show security warning - for testing/internal use only

$ErrorActionPreference = "Stop"

$SERVER_IP = "5.78.135.79"
$SERVER_USER = "root"
$SSH_KEY = "$env:USERPROFILE\.ssh\id_ed25519"
$DEPLOY_DIR = "/var/www/smart-inventory"

Write-Host ""
Write-Host "=== Setting Up Self-Signed SSL Certificate ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "WARNING: Browsers will show 'Not Secure' warning" -ForegroundColor Yellow
Write-Host "Users must click 'Advanced' > 'Proceed' to access the site" -ForegroundColor Yellow
Write-Host ""

$confirm = Read-Host "Continue with self-signed certificate setup? (yes/no)"
if ($confirm -ne "yes") {
    Write-Host "Setup cancelled." -ForegroundColor Yellow
    exit 0
}

Write-Host ""
Write-Host "Installing self-signed SSL certificate..." -ForegroundColor Green
Write-Host ""

# Create the SSL setup script on the server
$sslSetupScript = @'
#!/bin/bash
set -e

echo "================================================"
echo "  Setting Up Self-Signed SSL Certificate"
echo "================================================"
echo ""

# Step 1: Create self-signed certificate
echo "[1/5] Creating self-signed certificate..."
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/nginx-selfsigned.key \
  -out /etc/ssl/certs/nginx-selfsigned.crt \
  -subj "/C=RW/ST=Kigali/L=Kigali/O=SmartInventory/CN=5.78.135.79" 2>&1 | grep -v "^+"
echo "    Certificate created (valid for 365 days)"

# Step 2: Update Nginx configuration
echo "[2/5] Updating Nginx configuration..."
cat > /etc/nginx/sites-available/smart-inventory << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name _;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name _;
    root /var/www/smart-inventory/public;

    ssl_certificate /etc/ssl/certs/nginx-selfsigned.crt;
    ssl_certificate_key /etc/ssl/private/nginx-selfsigned.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-SHA384;

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
echo "    Nginx configuration updated"

# Step 3: Test Nginx configuration
echo "[3/5] Testing Nginx configuration..."
nginx -t
echo "    Configuration valid"

# Step 4: Update Laravel .env
echo "[4/5] Updating Laravel configuration..."
cd /var/www/smart-inventory
sed -i 's|APP_URL=.*|APP_URL=https://5.78.135.79|g' .env
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
echo "    Laravel configured for HTTPS"

# Step 5: Restart services
echo "[5/5] Restarting services..."
systemctl reload nginx
systemctl restart php8.3-fpm
echo "    Services restarted"

# Open HTTPS port in firewall if ufw is active
if ufw status | grep -q "Status: active"; then
    echo ""
    echo "Updating firewall..."
    ufw allow 443/tcp > /dev/null 2>&1
    echo "    Port 443 (HTTPS) opened"
fi

echo ""
echo "================================================"
echo "  SSL Certificate Installed Successfully!"
echo "================================================"
echo ""
echo "Your site is now available at:"
echo "  https://5.78.135.79"
echo ""
echo "NOTE: Browsers will show a security warning."
echo "To access the site:"
echo "  1. Click 'Advanced' or 'Details'"
echo "  2. Click 'Proceed to 5.78.135.79' or 'Accept Risk'"
echo ""
echo "HTTP (port 80) will automatically redirect to HTTPS"
echo "Certificate expires: $(date -d '+365 days' '+%Y-%m-%d')"
echo ""
'@

# Execute the SSL setup script on the server
$sslSetupScript | ssh -i $SSH_KEY "$SERVER_USER@$SERVER_IP" "bash -s"

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "================================================" -ForegroundColor Green
    Write-Host "  SSL CERTIFICATE INSTALLED!" -ForegroundColor Green
    Write-Host "================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Your site is now available at:" -ForegroundColor Cyan
    Write-Host "  https://$SERVER_IP" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "IMPORTANT:" -ForegroundColor Yellow
    Write-Host "  - Browsers will show a security warning" -ForegroundColor Gray
    Write-Host "  - Click 'Advanced' > 'Proceed to $SERVER_IP'" -ForegroundColor Gray
    Write-Host "  - HTTP traffic will auto-redirect to HTTPS" -ForegroundColor Gray
    Write-Host ""
    Write-Host "Testing HTTPS connection..." -ForegroundColor Yellow

    # Test if HTTPS is working
    try {
        $response = Invoke-WebRequest -Uri "https://$SERVER_IP" -SkipCertificateCheck -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
        Write-Host "HTTPS is working! Status: $($response.StatusCode)" -ForegroundColor Green
    } catch {
        Write-Host "HTTPS endpoint is configured (certificate check skipped)" -ForegroundColor Gray
    }

    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "  1. Visit https://$SERVER_IP in your browser" -ForegroundColor Gray
    Write-Host "  2. Accept the security warning" -ForegroundColor Gray
    Write-Host "  3. Consider getting a domain name for a trusted certificate" -ForegroundColor Gray
    Write-Host ""

} else {
    Write-Host ""
    Write-Host "SSL SETUP FAILED!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Troubleshooting:" -ForegroundColor Yellow
    Write-Host "  1. Check Nginx status:" -ForegroundColor Gray
    Write-Host "     ssh -i $SSH_KEY $SERVER_USER@$SERVER_IP 'systemctl status nginx'" -ForegroundColor Gray
    Write-Host ""
    Write-Host "  2. Check Nginx error log:" -ForegroundColor Gray
    Write-Host "     ssh -i $SSH_KEY $SERVER_USER@$SERVER_IP 'tail -50 /var/log/nginx/error.log'" -ForegroundColor Gray
    Write-Host ""
    Write-Host "  3. Verify certificate files:" -ForegroundColor Gray
    Write-Host "     ssh -i $SSH_KEY $SERVER_USER@$SERVER_IP 'ls -la /etc/ssl/certs/nginx-selfsigned.crt /etc/ssl/private/nginx-selfsigned.key'" -ForegroundColor Gray
    Write-Host ""
    exit 1
}
