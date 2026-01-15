#!/bin/bash
set -e

echo "🚀 Starting Laravel application setup..."

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL..."
until php -r "
try {
    new PDO('mysql:host=${DB_HOST};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
    exit(0);
} catch (PDOException \$e) {
    exit(1);
}
"; do
    sleep 2
done

echo "✓ MySQL is ready"

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force

# Run seeders
echo "🌱 Running seeders..."
php artisan db:seed --force

# Optimize Laravel
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache

echo "✅ Setup complete! Starting PHP-FPM..."

# Ensure PHP-FPM listens on 0.0.0.0:9000 for Nginx fastcgi_pass
if grep -q "^listen = 127.0.0.1:9000" /usr/local/etc/php-fpm.d/www.conf 2>/dev/null; then
    echo "🔧 Adjusting php-fpm listen address to 9000 (all interfaces)"
    sed -i 's/^listen = 127.0.0.1:9000/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf || true
fi

# Tune PHP-FPM pool to avoid 504 timeouts under moderate load
if [ -f /usr/local/etc/php-fpm.d/www.conf ]; then
    echo "🔧 Tuning PHP-FPM pool settings (pm.max_children, request_terminate_timeout, etc.)"
    sed -i 's/^;*pm.max_children.*/pm.max_children = 20/' /usr/local/etc/php-fpm.d/www.conf || true
    sed -i 's/^;*pm.start_servers.*/pm.start_servers = 5/' /usr/local/etc/php-fpm.d/www.conf || true
    sed -i 's/^;*pm.min_spare_servers.*/pm.min_spare_servers = 5/' /usr/local/etc/php-fpm.d/www.conf || true
    sed -i 's/^;*pm.max_spare_servers.*/pm.max_spare_servers = 10/' /usr/local/etc/php-fpm.d/www.conf || true
    sed -i 's/^;*pm.max_requests.*/pm.max_requests = 500/' /usr/local/etc/php-fpm.d/www.conf || true
    # prevent extremely long-running requests from blocking workers
    if grep -q '^;*request_terminate_timeout' /usr/local/etc/php-fpm.d/www.conf; then
        sed -i 's/^;*request_terminate_timeout.*/request_terminate_timeout = 60s/' /usr/local/etc/php-fpm.d/www.conf || true
    else
        echo 'request_terminate_timeout = 60s' >> /usr/local/etc/php-fpm.d/www.conf
    fi
fi

# Start PHP-FPM
exec php-fpm
