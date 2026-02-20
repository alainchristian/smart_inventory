#!/bin/bash

# LEMP Stack Installation Script for Laravel
# Server: Hetzner Ubuntu 24.04.4 LTS

set -e  # Exit on error

echo "========================================="
echo "LEMP Stack Installation Started"
echo "========================================="
echo ""

# 1. System Update
echo "[1/12] Updating system..."
export DEBIAN_FRONTEND=noninteractive
apt update -y > /dev/null 2>&1
apt upgrade -y > /dev/null 2>&1
apt install -y software-properties-common curl wget git unzip > /dev/null 2>&1
echo "✓ System updated"

# 2. Install Nginx
echo "[2/12] Installing Nginx..."
apt install -y nginx > /dev/null 2>&1
systemctl enable nginx > /dev/null 2>&1
systemctl start nginx
echo "✓ Nginx installed"

# 3. Install PHP 8.3
echo "[3/12] Installing PHP 8.3 and extensions..."
add-apt-repository ppa:ondrej/php -y > /dev/null 2>&1
apt update -y > /dev/null 2>&1
apt install -y php8.3-fpm php8.3-cli php8.3-common php8.3-pgsql \
    php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath \
    php8.3-gd php8.3-intl php8.3-redis php8.3-opcache > /dev/null 2>&1
systemctl enable php8.3-fpm > /dev/null 2>&1
systemctl start php8.3-fpm
echo "✓ PHP 8.3 installed ($(php -v | head -1))"

# 4. Install PostgreSQL 16
echo "[4/12] Installing PostgreSQL 16..."
sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - > /dev/null 2>&1
apt update -y > /dev/null 2>&1
apt install -y postgresql-16 postgresql-contrib-16 > /dev/null 2>&1
systemctl enable postgresql > /dev/null 2>&1
systemctl start postgresql
echo "✓ PostgreSQL 16 installed ($(sudo -u postgres psql --version))"

# 5. Install Composer
echo "[5/12] Installing Composer..."
curl -sS https://getcomposer.org/installer | php > /dev/null 2>&1
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
echo "✓ Composer installed ($(composer --version --no-ansi | head -1))"

# 6. Install Node.js 20.x
echo "[6/12] Installing Node.js 20.x..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash - > /dev/null 2>&1
apt install -y nodejs > /dev/null 2>&1
echo "✓ Node.js installed ($(node -v), npm $(npm -v))"

# 7. Install Supervisor
echo "[7/12] Installing Supervisor..."
apt install -y supervisor > /dev/null 2>&1
systemctl enable supervisor > /dev/null 2>&1
systemctl start supervisor
echo "✓ Supervisor installed"

# 8. Install Certbot
echo "[8/12] Installing Certbot..."
apt install -y certbot python3-certbot-nginx > /dev/null 2>&1
echo "✓ Certbot installed"

# 9. Configure Firewall
echo "[9/12] Configuring UFW firewall..."
ufw --force enable > /dev/null 2>&1
ufw allow OpenSSH > /dev/null 2>&1
ufw allow 'Nginx Full' > /dev/null 2>&1
echo "✓ Firewall configured"

# 10. Install Redis
echo "[10/12] Installing Redis..."
apt install -y redis-server > /dev/null 2>&1
systemctl enable redis-server > /dev/null 2>&1
systemctl start redis-server
echo "✓ Redis installed"

# 11. Configure PHP for Production
echo "[11/12] Configuring PHP for production..."
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 20M/' /etc/php/8.3/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 20M/' /etc/php/8.3/fpm/php.ini
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.3/fpm/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.3/fpm/php.ini
sed -i 's/;opcache.enable=.*/opcache.enable=1/' /etc/php/8.3/fpm/php.ini
sed -i 's/;opcache.memory_consumption=.*/opcache.memory_consumption=256/' /etc/php/8.3/fpm/php.ini
systemctl restart php8.3-fpm
echo "✓ PHP configured for production"

# 12. Set Timezone
echo "[12/12] Setting timezone to Africa/Kigali..."
timedatectl set-timezone Africa/Kigali
echo "✓ Timezone set to $(timedatectl | grep 'Time zone' | awk '{print $3}')"

echo ""
echo "========================================="
echo "LEMP Stack Installation Completed!"
echo "========================================="
echo ""
echo "Installed software versions:"
echo "  - Nginx: $(nginx -v 2>&1 | cut -d'/' -f2)"
echo "  - PHP: $(php -v | head -1 | cut -d' ' -f2)"
echo "  - PostgreSQL: $(sudo -u postgres psql --version | cut -d' ' -f3)"
echo "  - Composer: $(composer --version --no-ansi | head -1 | cut -d' ' -f3)"
echo "  - Node.js: $(node -v)"
echo "  - npm: $(npm -v)"
echo ""
echo "All services are running and configured."
echo "Server is ready for Laravel deployment!"
