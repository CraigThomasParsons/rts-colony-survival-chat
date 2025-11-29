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

# Start PHP-FPM
exec php-fpm
