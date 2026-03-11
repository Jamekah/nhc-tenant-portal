#!/bin/sh
set -e

echo "=== NHC Portal Startup ==="

# Clear any stale config cache from build time
echo "Clearing config cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Cache config with real environment variables (optional - don't fail if it errors)
echo "Caching config..."
php artisan optimize || echo "Warning: optimize failed, continuing without cache"
php artisan filament:optimize || echo "Warning: filament:optimize failed, continuing"

# Create storage link if not exists
php artisan storage:link || true

echo "Starting server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
