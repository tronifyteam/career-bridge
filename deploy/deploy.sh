#!/bin/bash
#
# Deployment script for Migrant Work TW Backend
# Hostinger VPS (Highest Tier)
#
# Usage: bash deploy/deploy.sh
#

set -e

APP_DIR="/var/www/migrant_work_tw_be"
REPO_URL="https://github.com/ArtaRizki/migrant_work_tw_be.git"

echo "========================================="
echo "  Migrant Work TW — Deployment Script"
echo "========================================="

# 1. Pull latest code
echo ""
echo "[1/8] Pulling latest code..."
cd $APP_DIR
git pull origin main

# 2. Install dependencies
echo ""
echo "[2/8] Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Clear caches
echo ""
echo "[3/8] Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Run migrations & seed
echo ""
echo "[4/8] Running migrations and seeding..."
php artisan migrate --force
php artisan db:seed --force

# 5. Optimize for production
echo ""
echo "[5/8] Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Set permissions
echo ""
echo "[6/8] Setting permissions..."
sudo chown -R deployer:www-data $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache

# 7. Restart services
echo ""
echo "[7/8] Restarting services..."
sudo supervisorctl reread || true
sudo supervisorctl update || true
sudo supervisorctl restart migrant-worker-queue:* || true
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

# 8. Health check
echo ""
echo "[8/8] Running health check..."
php artisan about

echo ""
echo "========================================="
echo "  Deployment completed successfully!"
echo "========================================="
echo ""
echo "API URL: https://yourdomain.com/api"
echo "Admin:   https://yourdomain.com/admin"
echo ""
