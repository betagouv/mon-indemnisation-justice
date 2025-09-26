#!/bin/bash

ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/.. && pwd)

# Si INSTANCE_NUMBER n'est pas défini ou vaut 0, alors jouer la migration de base de données
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == 0 ]; then
  "${ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing
fi