#!/bin/bash

ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/.. && pwd)

echo "Démarrage de l'instance de ${APP_ENV} #${INSTANCE_NUMBER}";

# Si INSTANCE_NUMBER vaut 0, alors jouer la migration de base de données
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == '0' ]; then
  "${ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing

  "${ROOT}/bin/console" doctrine:fixtures:load --no-interaction --purge-exclusions=geo_codes_postaux,geo_communes,geo_departements,geo_pays,geo_regions,fdo_etablissements,fdo_etablissements_code_postaux
fi

frankenphp php-server -r /app/public -a /app/public/index.php
