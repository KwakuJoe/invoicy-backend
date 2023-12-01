#!/usr/bin/env bash
echo "Running composer"
composer global require hirak/prestissimo
composer install --no-dev --working-dir=/var/www/html

echo "generating application key..."
php artisan key:generate --show

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Creating Queue table..."
php artisan queue:table

echo "Running migrations..."
php artisan migrate --force

echo "Queuing intiated ..."
php artisan queue:work


