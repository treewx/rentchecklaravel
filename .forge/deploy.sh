#!/usr/bin/env bash

# Laravel Forge Deployment Script for Rent Tracker
# This script runs automatically when you deploy via Forge

cd /home/forge/your-domain.com

# Turn on maintenance mode
php artisan down || true

# Pull the latest changes from the git repository
git pull origin $FORGE_SITE_BRANCH

# Install/update composer dependencies
$FORGE_COMPOSER install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Run database migrations (if any)
php artisan migrate --force

# Clear caches and optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Queue: restart workers to pick up new code
php artisan queue:restart

# Turn off maintenance mode
php artisan up

echo "🎉 Rent Tracker deployment completed successfully!"