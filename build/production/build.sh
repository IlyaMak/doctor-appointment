touch .env
mkdir -p var/cache
chown -R www-data:www-data var
composer install --no-dev --no-interaction --no-scripts
npm i
npm run build
