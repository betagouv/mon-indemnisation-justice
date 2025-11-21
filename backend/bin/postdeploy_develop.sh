#!/bin/bash

ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/.. && pwd)

# Si INSTANCE_NUMBER n'est pas défini ou vaut 0, alors jouer la migration de base de données
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == 0 ]; then
  "${ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing
fi


"${ROOT}/bin/console" doctrine:fixture:load --no-interaction \
  --purge-exclusions=geo_codes_postaux \
  --purge-exclusions=geo_communes \
  --purge-exclusions=geo_departements \
  --purge-exclusions=geo_pays \
  --purge-exclusions=geo_regions \
  --purge-with-truncate