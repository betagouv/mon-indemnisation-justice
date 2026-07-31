#!/bin/bash

ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/.. && pwd)

echo "Démarrage de l'instance de ${APP_ENV} #${INSTANCE_NUMBER}";

# Si INSTANCE_NUMBER vaut 0, alors :
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == '0' ]; then
  # 1: Supprimer les tables existantes
  # "${ROOT}/bin/console" doctrine:schema:drop --force; # Ne fonctionne pas, laisse parfois quelques tables
  tables=$(echo "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' and table_type = 'BASE TABLE' and table_name <> 'spatial_ref_sys'"  | psql -t "${DATABASE_URL/\?*/}");
  for table in $tables; do
    echo "DROP TABLE IF EXISTS $table CASCADE" | psql "${DATABASE_URL/\?*/}";
  done

  # 2: jouer les migrations Doctrine
  "${ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing;

  # 3: charger les données de test
  "${ROOT}/bin/console" doctrine:fixtures:load --no-interaction --purge-exclusions=geo_codes_postaux,geo_communes,geo_departements,geo_pays,geo_regions,fdo_etablissements,fdo_etablissements_code_postaux
fi

frankenphp php-server -r /app/public -a /app/public/index.php
