#!/bin/bash

echo "Démarrage de l'instance #${INSTANCE_NUMBER} sur l'environnement ${APP_ENV}";

cd /app

composer install --no-interaction --optimize-autoloader

yarn install
npx puppeteer browsers install firefox

bin/console doctrine:migration:migrate --no-interaction --all-or-nothing

exec "$@"
