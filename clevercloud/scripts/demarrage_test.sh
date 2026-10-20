#!/bin/bash

# ROOT fait référence à la racine du dépôt
ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/../.. && pwd)
SYMFONY_ROOT="${ROOT}/backend"

echo "Démarrage de l'instance #${INSTANCE_NUMBER} sur l'environnement ${APP_ENV}";

# Si INSTANCE_NUMBER vaut 0, alors :
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == '0' ]; then
  # 1: jouer la migration de base de données
  "${SYMFONY_ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing
  # 2: charger les données de provision
  "${SYMFONY_ROOT}/bin/console" doctrine:fixtures:load --no-interaction --purge-with-truncate
fi

frankenphp php-server -r /app/public -a /app/public/index.php
