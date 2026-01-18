#!/bin/bash
set -e

# Fix permissions on the entire project directory
chown -R laravel:www-data /var/www
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Install composer dependencies if vendor folder is missing
if [ ! -d "/var/www/vendor" ]; then
    echo "Installing Composer dependencies..."
    su laravel -c "composer install --no-interaction"
fi

# Install npm dependencies if node_modules folder is missing
if [ ! -d "/var/www/node_modules" ]; then
    echo "Installing NPM dependencies..."
    su laravel -c "npm install"
fi

# Build assets if manifest is missing
if [ ! -f "/var/www/public/build/manifest.json" ]; then
    echo "Building frontend assets..."
    su laravel -c "npm run build"
fi

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
