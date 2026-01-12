#!/bin/bash

ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/.. && pwd)

echo "Démarrage de l'instance de ${APP_ENV} #${INSTANCE_NUMBER}";

# Si INSTANCE_NUMBER vaut 0, alors jouer la migration de base de données
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == '0' ]; then
  "${ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing
fi

frankenphp php-server -r /app/public -a /app/public/index.php
