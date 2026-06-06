#!/bin/bash

# Function to check if a command exists
command_exists() {
  command -v "$1" >/dev/null 2>&1
}

if [ "$EUID" -ne 0 ]; then
  echo "Please run as root."
  exit 1
fi

# Check if Node.js is installed
if command_exists node; then
  echo "Node.js is already installed."
else
  echo "Node.js is not installed. Installing Node.js version 20..."

  # Install Node.js version 20 using NodeSource
  curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
  sudo apt-get install -y nodejs

  if command_exists node; then
    echo "Node.js version 20 has been successfully installed."
  else
    echo "Failed to install Node.js version 20. Exiting..."
    exit 1
  fi
fi

# Check if Yarn is installed
if command_exists yarn; then
  echo "Yarn is already installed."
else
  echo "Yarn is not installed. Installing Yarn..."
  npm install -g yarn

  if command_exists yarn; then
    echo "Yarn has been successfully installed."
  else
    echo "Failed to install Yarn. Exiting..."
    exit 1
  fi
fi

echo "Installing Yarn packages..."
if [ -f "yarn.lock" ]; then
  yarn install
else
  echo "yarn.lock file not found. Exiting..."
  echo "make sure you are in the correct directory (/var/www/pterodactyl by default)"
  exit 1
fi

# Install ainx
npm install -g ainx

echo "ainx has been successfully installed."