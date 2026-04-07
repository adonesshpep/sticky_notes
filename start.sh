#!/bin/bash
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
composer dump-autoload
php artisan optimize:clear
php artisan migrate --force
# php artisan migrate:refresh
# php artisan db:seed --force
php artisan storage:link
apache2-foreground