#!/bin/bash
# set -e

# Cache configuration
echo "Caching Laravel configuration..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations
# IMPORTANT: In a production environment with multiple instances (like Cloud Run),
# running migrations automatically on every instance startup can cause race conditions.
# For simplicity in this blueprint, it's included, but normally you'd run this as a separate Cloud Build step.
echo "Running database migrations..."
# php artisan migrate --force || true

# Pass control to the CMD (apache2-foreground)
exec "$@"
