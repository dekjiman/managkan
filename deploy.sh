#!/bin/bash

# ManagPro Backend Deployment Script
# Run this on cPanel terminal after uploading files

echo "=== ManagPro Backend Deployment ==="

# Navigate to project directory
cd ~/public_html/apimanagpro.matamaya.id

# Install dependencies
echo "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader

# Set permissions
echo "Setting permissions..."
chmod -R 755 writable/
chmod 644 .env
chmod 644 .htaccess

# Clear cache
echo "Clearing cache..."
php spark cache:clear

# Run migrations
echo "Running migrations..."
php spark migrate

echo "=== Deployment Complete ==="
echo "Visit: https://apimanagpro.matamaya.id/"
echo "API Docs: https://apimanagpro.matamaya.id/docs"
