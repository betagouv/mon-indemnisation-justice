#!/bin/bash

ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/.. && pwd)

echo "Démarrage de l'instance de ${APP_ENV} #${INSTANCE_NUMBER}";

# Si INSTANCE_NUMBER vaut 0, alors :
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == '0' ]; then
  # 1: jouer la migration de base de données
  "${ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing
  # 2: charger les données de provision
  "${ROOT}/bin/console" doctrine:fixtures:load --group=develop --no-interaction --purge-exclusions=geo_codes_postaux,geo_communes,geo_departements,geo_pays,geo_regions,agent_fournisseurs_identites
fi

frankenphp php-server -r /app/public -a /app/public/index.php
