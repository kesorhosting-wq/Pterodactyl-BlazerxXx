#!/bin/bash

# Pterodactyl BlazerxXx One-Time Installer
# For Ubuntu/Debian

set -e

echo "🚀 Starting Pterodactyl BlazerxXx Installer..."

# 1. Update System
sudo apt update && sudo apt upgrade -y

# 2. Check for dependencies
echo "📦 Checking system dependencies..."
dependencies=(php mysql nginx composer node yarn)
for cmd in "${dependencies[@]}"; do
    if ! command -v $cmd &> /dev/null; then
        echo "❌ $cmd is not installed. Please install it first."
        exit 1
    fi
done

# 3. Setup Project
echo "📂 Setting up project files..."
mkdir -p /var/www/pterodactyl
cd /var/www/pterodactyl

# 4. Install PHP Dependencies
echo "🐘 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 5. Environment Setup
if [ ! -f .env ]; then
    echo "📄 Creating .env file..."
    cp .env.example .env
    php artisan key:generate --force
    echo "⚠️  ACTION REQUIRED: Edit .env and set your database credentials, then run 'php artisan migrate --seed'"
fi

# 6. Install Node Dependencies & Build
echo "📦 Installing Node dependencies..."
yarn install --production --frozen-lockfile

echo "🏗️ Building assets (this may take a while)..."
yarn build:production

# 7. Permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/pterodactyl/*
chmod -R 755 storage/* bootstrap/cache

# 8. Blueprint Initialization
if [ -f blueprint.sh ]; then
    echo "🧩 Initializing Blueprint..."
    bash blueprint.sh
fi

echo "✅ Installation phase complete!"
echo "-------------------------------------------------------"
echo "NEXT STEPS:"
echo "1. Configure your Database in .env"
echo "2. Run: php artisan migrate --seed"
echo "3. Run: php artisan p:user:make"
echo "4. Setup Nginx and Cron as per Pterodactyl documentation."
echo "-------------------------------------------------------"
