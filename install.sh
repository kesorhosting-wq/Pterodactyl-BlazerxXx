#!/bin/bash

# Pterodactyl BlazerxXx Interactive Installer
# Based on the community installer style
# Copyright (C) 2026, Kesor Hosting / BlazerxXx

set -e

# --- Colors ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# --- Configuration ---
GITHUB_REPO="https://github.com/kesorhosting-wq/Pterodactyl-BlazerxXx.git"
PANEL_PATH="/var/www/pterodactyl"

# --- Functions ---
print_status() { echo -e "${YELLOW}* $1${NC}"; }
print_success() { echo -e "${GREEN}SUCCESS: $1${NC}"; }
print_error() { echo -e "${RED}ERROR: $1${NC}"; }

check_root() {
    if [[ $EUID -ne 0 ]]; then
       print_error "This script must be run as root."
       exit 1
    fi
}

welcome() {
    clear
    echo -e "${BLUE}#######################################################${NC}"
    echo -e "${BLUE}#                                                     #${NC}"
    echo -e "${BLUE}#        Pterodactyl BlazerxXx Installer v1.2.0       #${NC}"
    echo -e "${BLUE}#                                                     #${NC}"
    echo -e "${BLUE}#######################################################${NC}"
    echo ""
}

install_panel() {
    print_status "Starting Panel installation..."
    
    # Dependencies
    print_status "Installing system dependencies (PHP 8.3, MariaDB, Nginx)..."
    apt update && apt upgrade -y
    apt install -y software-properties-common curl gnupg2 ca-certificates lsb-release
    add-apt-repository -y ppa:ondrej/php
    apt update
    apt install -y php8.3 php8.3-{common,cli,gd,mysql,mbstring,bcmath,xml,fpm,curl,zip} mariadb-server nginx redis-server git tar

    # Composer
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

    # Node.js
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt install -y nodejs
    npm install --global yarn

    # Database
    read -p "* Enter a secure password for the 'pterodactyl' database user: " DB_PASSWORD
    mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS panel;
CREATE USER IF NOT EXISTS 'pterodactyl'@'127.0.0.1' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON panel.* TO 'pterodactyl'@'127.0.0.1' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF

    # Code
    print_status "Cloning repository..."
    rm -rf $PANEL_PATH
    mkdir -p $PANEL_PATH
    git clone $GITHUB_REPO $PANEL_PATH
    cd $PANEL_PATH

    print_status "Installing app dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
    
    cp .env.example .env
    sed -i "s/DB_PASSWORD=/DB_PASSWORD=$DB_PASSWORD/" .env
    php artisan key:generate --force
    php artisan migrate --seed --force

    print_status "Building assets (yarn)..."
    yarn install --production
    yarn build:production

    # SSL
    read -p "* Enter your Domain (e.g. panel.example.com): " DOMAIN
    read -p "* Enter your Email (for SSL expiry): " EMAIL
    
    apt install -y certbot python3-certbot-nginx
    certbot --nginx -d $DOMAIN --non-interactive --agree-tos -m $EMAIL

    # Nginx Config
    cp nginx.conf.example /etc/nginx/sites-available/pterodactyl.conf
    sed -i "s/<domain>/$DOMAIN/g" /etc/nginx/sites-available/pterodactyl.conf
    sed -i "s/php8.1-fpm.sock/php8.3-fpm.sock/g" /etc/nginx/sites-available/pterodactyl.conf
    ln -sf /etc/nginx/sites-available/pterodactyl.conf /etc/nginx/sites-enabled/pterodactyl.conf
    systemctl restart nginx

    # Permissions & Services
    chown -R www-data:www-data $PANEL_PATH/*
    (crontab -l 2>/dev/null; echo "* * * * * php $PANEL_PATH/artisan schedule:run >> /dev/null 2>&1") | crontab -
    
    cp pteroq.service.example /etc/systemd/system/pteroq.service
    systemctl enable --now pteroq.service

    # Blueprint
    if [ -f blueprint.sh ]; then
        bash blueprint.sh
    fi

    print_success "Panel installation complete!"
}

install_wings() {
    print_status "Starting Wings installation..."
    # Official Wings Install Logic
    curl -sSL https://get.docker.com/ | CHANNEL=stable sh
    systemctl enable --now docker
    
    mkdir -p /etc/pterodactyl
    curl -L -o /usr/local/bin/wings "https://github.com/pterodactyl/wings/releases/latest/download/wings_linux_amd64"
    chmod +x /usr/local/bin/wings
    
    print_success "Wings binary installed. Please go to the panel to finish configuration."
}

# --- Main Logic ---
check_root
welcome

options=(
    "Install the Panel (BlazerxXx Fork)"
    "Install Wings"
    "Install Both (Panel & Wings)"
    "Exit"
)

echo "What would you like to do?"
for i in "${!options[@]}"; do
    echo "[$i] ${options[$i]}"
done

echo -n "* Input 0-$((${#options[@]} - 1)): "
read -r action

case $action in
    0)
        install_panel
        ;;
    1)
        install_wings
        ;;
    2)
        install_panel
        install_wings
        ;;
    3)
        exit 0
        ;;
    *)
        print_error "Invalid option"
        exit 1
        ;;
esac

print_success "Done!"
