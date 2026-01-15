#!/bin/bash

ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/.. && pwd)

echo "Démarrage de l'instance de ${APP_ENV} #${INSTANCE_NUMBER}";

# Si INSTANCE_NUMBER vaut 0, alors :
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == '0' ]; then
  # 1: jouer la migration de base de données
  "${ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing
  # 2: charger les données de provision
  "${ROOT}/bin/console" doctrine:fixtures:load --no-interaction --purge-with-truncate
fi

frankenphp php-server -r /app/public -a /app/public/index.php
