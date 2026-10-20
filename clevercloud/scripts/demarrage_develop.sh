#!/bin/bash

# ROOT fait référence à la racine du dépôt
ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/../.. && pwd)
SYMFONY_ROOT="${ROOT}/backend"

echo "Démarrage de l'instance #${INSTANCE_NUMBER} sur l'environnement ${APP_ENV}";

# Si INSTANCE_NUMBER vaut 0, alors :
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == '0' ]; then
  # 1: Supprimer les tables existantes
  tables=$(echo "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' and table_type = 'BASE TABLE' and table_name <> 'spatial_ref_sys'"  | psql -t "${DATABASE_URL/\?*/}");
  for table in $tables; do
    echo "DROP TABLE IF EXISTS $table CASCADE" | psql "${DATABASE_URL/\?*/}";
  done

  # 2: jouer les migrations Doctrine
  "${SYMFONY_ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing;

  # 3: charger les données de test
  "${SYMFONY_ROOT}/bin/console" doctrine:fixtures:load --no-interaction --purge-exclusions=geo_codes_postaux,geo_communes,geo_departements,geo_pays,geo_regions,fdo_etablissements,fdo_etablissements_code_postaux
fi

frankenphp php-server -r /app/public -a /app/public/index.php
