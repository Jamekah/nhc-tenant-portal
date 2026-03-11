#!/bin/sh

echo "=== NHC Portal Startup ==="
echo "PORT=${PORT:-8080}"

# Clear any stale config cache from build time
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run database migrations
php artisan migrate --force || true

# Start the server
echo "Starting server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
