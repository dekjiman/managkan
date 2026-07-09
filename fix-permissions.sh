#!/bin/bash
# Fix file permissions for CodeIgniter 4 on cPanel
# Run this via cPanel Terminal or SSH

cd /home/matamaya/public_html/managkan/backend

# Set directory permissions to 755
find . -type d -exec chmod 755 {} \;

# Set file permissions to 644
find . -type f -exec chmod 644 {} \;

# Make specific files executable if needed
chmod 755 spark

echo "Permissions fixed successfully!"
echo "Directories: 755"
echo "Files: 644"
