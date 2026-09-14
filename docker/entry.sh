#!/bin/bash
set -e

# Default to 80 if PORT is not set by environment (Render supplies $PORT dynamically)
PORT="${PORT:-80}"
echo "==> Configuring Apache to listen on port ${PORT}..."

# Update Apache port configurations
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# SQLite fallback initialization if DB_CONNECTION is sqlite
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    echo "==> Preparing SQLite database..."
    mkdir -p /var/www/html/database
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        touch /var/www/html/database/database.sqlite
    fi
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
fi

# Ensure all storage and cache directories exist
echo "==> Setting directory permissions..."
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate application key if missing
if [ -z "$APP_KEY" ]; then
    echo "==> APP_KEY is not set. Generating application key..."
    php artisan key:generate --force || true
fi

# Run database migrations
if [ "$RUN_MIGRATIONS" != "false" ]; then
    echo "==> Running database migrations..."
    php artisan migrate --force || echo "Migration skipped or database already up-to-date."
fi

# Run seeders if RUN_SEEDER is enabled
if [ "$RUN_SEEDER" = "true" ]; then
    echo "==> Seeding movie database (1990 - 2026)..."
    php artisan db:seed --force || echo "Seeder execution completed."
fi

# Cache configuration, routes, and views for production performance
echo "==> Caching Laravel optimization assets..."
php artisan config:cache || php artisan config:clear
php artisan route:cache || php artisan route:clear
php artisan view:cache || php artisan view:clear

echo "==> Ready! Starting Apache web server on port ${PORT}..."
exec apache2-foreground
