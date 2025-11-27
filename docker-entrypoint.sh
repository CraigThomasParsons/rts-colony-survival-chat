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

# Start PHP-FPM
exec php-fpm
