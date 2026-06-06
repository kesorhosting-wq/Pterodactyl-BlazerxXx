#!/bin/bash

# Pterodactyl BlazerxXx Ultimate Installer
# Based on the style of the official community installer
# Copyright (C) 2026, Kesor Hosting / BlazerxXx

set -e

# --- Configuration ---
GITHUB_REPO="https://github.com/kesorhosting-wq/Pterodactyl-BlazerxXx.git"
PANEL_PATH="/var/www/pterodactyl"

# --- Colors ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# --- Functions ---
print_error() { echo -e "${RED}ERROR: $1${NC}"; }
print_success() { echo -e "${GREEN}SUCCESS: $1${NC}"; }
print_status() { echo -e "${YELLOW}* $1${NC}"; }

check_root() {
    if [[ $EUID -ne 0 ]]; then
       print_error "This script must be run as root."
       exit 1
    fi
}

install_dependencies() {
    print_status "Updating system and installing dependencies..."
    apt update && apt upgrade -y
    apt install -y software-properties-common curl apt-transport-https ca-certificates gnupg

    # PHP 8.3 (Ondrej)
    add-apt-repository -y ppa:ondrej/php
    apt update
    apt install -y php8.3 php8.3-{common,cli,gd,mysql,mbstring,bcmath,xml,fpm,curl,zip}

    # MariaDB
    apt install -y mariadb-server

    # Nginx
    apt install -y nginx

    # Redis
    apt install -y redis-server

    # Composer
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

    # Node.js & Yarn
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt install -y nodejs
    npm install --global yarn
}

setup_database() {
    print_status "Configuring Database..."
    read -p "Enter Database Password for 'pterodactyl' user: " DB_PASSWORD
    
    mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS panel;
CREATE USER IF NOT EXISTS 'pterodactyl'@'127.0.0.1' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON panel.* TO 'pterodactyl'@'127.0.0.1' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF
    print_success "Database created."
}

setup_panel() {
    print_status "Cloning Pterodactyl BlazerxXx..."
    rm -rf $PANEL_PATH
    mkdir -p $PANEL_PATH
    git clone $GITHUB_REPO $PANEL_PATH
    cd $PANEL_PATH

    print_status "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction

    print_status "Configuring environment..."
    cp .env.example .env
    sed -i "s/DB_PASSWORD=/DB_PASSWORD=$DB_PASSWORD/" .env
    php artisan key:generate --force

    print_status "Running Migrations..."
    php artisan migrate --seed --force

    print_status "Building Frontend Assets..."
    yarn install --production
    yarn build:production

    print_status "Setting permissions..."
    chown -R www-data:www-data $PANEL_PATH/*
    chmod -R 755 storage/* bootstrap/cache
}

setup_ssl() {
    print_status "Configuring SSL (Certbot)..."
    read -p "Enter your domain (e.g. panel.example.com): " DOMAIN
    read -p "Enter your email for SSL alerts: " EMAIL

    apt install -y certbot python3-certbot-nginx
    certbot --nginx -d $DOMAIN --non-interactive --agree-tos -m $EMAIL

    print_status "Finalizing Nginx config..."
    # We use our nginx example but inject the domain and PHP socket
    cp nginx.conf.example /etc/nginx/sites-available/pterodactyl.conf
    sed -i "s/<domain>/$DOMAIN/g" /etc/nginx/sites-available/pterodactyl.conf
    # Ensure PHP socket matches
    sed -i "s/php8.1-fpm.sock/php8.3-fpm.sock/g" /etc/nginx/sites-available/pterodactyl.conf
    
    ln -sf /etc/nginx/sites-available/pterodactyl.conf /etc/nginx/sites-enabled/pterodactyl.conf
    systemctl restart nginx
}

setup_services() {
    print_status "Setting up Cron and Queue Worker..."
    (crontab -l 2>/dev/null; echo "* * * * * php $PANEL_PATH/artisan schedule:run >> /dev/null 2>&1") | crontab -
    
    cp pteroq.service.example /etc/systemd/system/pteroq.service
    systemctl enable --now pteroq.service
}

# --- Main Execution ---
clear
echo -e "${GREEN}#######################################################${NC}"
echo -e "${GREEN}#                                                     #${NC}"
echo -e "${GREEN}#        Pterodactyl BlazerxXx Installer v1.2.0       #${NC}"
echo -e "${GREEN}#                                                     #${NC}"
echo -e "${GREEN}#######################################################${NC}"
echo ""

check_root

echo "This script will install Pterodactyl, MariaDB, Nginx, SSL, and all dependencies."
read -p "Do you want to proceed? (y/N): " CONFIRM
[[ "$CONFIRM" =~ ^[Yy]$ ]] || exit 1

install_dependencies
setup_database
setup_panel
setup_ssl
setup_services

# Blueprint Init
if [ -f blueprint.sh ]; then
    bash blueprint.sh
fi

print_success "Installation Complete!"
echo "-------------------------------------------------------"
echo "URL: https://$DOMAIN"
echo "Run 'php artisan p:user:make' to create your admin account."
echo "-------------------------------------------------------"
