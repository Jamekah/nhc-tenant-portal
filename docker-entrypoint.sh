#!/bin/sh

echo "=== NHC Portal Startup ==="
echo "PORT is: ${PORT:-not set, defaulting to 8080}"

# Run database migrations
php artisan migrate --force 2>&1 || true

# Start the server using PHP's built-in server directly
echo "Starting PHP server on 0.0.0.0:${PORT:-8080}..."
exec php -S 0.0.0.0:${PORT:-8080} -t public 2>&1
