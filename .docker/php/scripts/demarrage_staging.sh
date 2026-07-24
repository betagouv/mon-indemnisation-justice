#!/bin/bash

ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/.. && pwd)

echo "Démarrage de l'instance de ${APP_ENV} #${INSTANCE_NUMBER}";

# Si INSTANCE_NUMBER vaut 0, alors jouer la migration de base de données
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == '0' ]; then
  # 1: Supprimer les tables existantes
  # "${ROOT}/bin/console" doctrine:schema:drop --force; # Ne fonctionne pas, laisse parfois quelques tables
  tables=$(echo "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' and table_type = 'BASE TABLE' and table_name <> 'spatial_ref_sys'"  | psql -t "${DATABASE_URL/\?*/}");
  for table in $tables; do
    echo "DROP TABLE IF EXISTS $table CASCADE" | psql "${DATABASE_URL/\?*/}";
  done

  # Afficher la liste des tables
  echo "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' and table_type = 'BASE TABLE' and table_name <> 'spatial_ref_sys'"  | psql -t "${DATABASE_URL/\?*/}" ;

  # 2: run Doctrine migrations
  "${ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing;

  # Afficher la liste des tables
  echo "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' and table_type = 'BASE TABLE' and table_name <> 'spatial_ref_sys'"  | psql -t "${DATABASE_URL/\?*/}" ;

  # 3 : provision agents
  psql "${DATABASE_URL/\?*/}" << EOF
insert into agents (email, nom, prenom, roles, identifiant, uid, administration_code, est_valide, date_creation) values
    ('gestion1@mailou.org', 'Gestion', 'Un', 'ROLE_AGENT,ROLE_AGENT_GESTION_PERSONNEL', '28e1857f-26f6-4988-8d01-d984f46dab6e', '0101', 'MJ', true, now() - interval '1 day'),
    ('gestion2@mailou.org', 'Gestion', 'Deux', 'ROLE_AGENT,ROLE_AGENT_GESTION_PERSONNEL', 'abba606c-dab7-402d-a6a6-71ff71ee78db', '0102', 'MJ', true, now() - interval '1 day'),
    ('attributeur1@mailou.org', 'Attributeur', 'Un', 'ROLE_AGENT,ROLE_AGENT_DOSSIER,ROLE_AGENT_ATTRIBUTEUR', '20661950-ed2a-4c1b-9f65-ebaef3946465', '0201', 'MJ', true, now() - interval '1 day'),
    ('attributeur2@mailou.org', 'Attributeur', 'Deux', 'ROLE_AGENT,ROLE_AGENT_DOSSIER,ROLE_AGENT_ATTRIBUTEUR', 'd427e344-14df-466e-b265-e2c4c294a868', '0202', 'MJ', true, now() - interval '1 day'),
    ('redacteur1@mailou.org', 'Rédacteur', 'Un', 'ROLE_AGENT,ROLE_AGENT_DOSSIER,ROLE_AGENT_REDACTEUR', '1d49b1ba-5dae-4e1c-9ee4-cdfb126be47f', '0301', 'MJ', true, now() - interval '1 day'),
    ('redacteur2@mailou.org', 'Rédacteur', 'Deux', 'ROLE_AGENT,ROLE_AGENT_DOSSIER,ROLE_AGENT_REDACTEUR', '4d9e629b-a230-45b0-a4e7-b90b28bb591c', '0302', 'MJ', true, now() - interval '1 day'),
    ('validateur1@mailou.org', 'Validateur', 'Un', 'ROLE_AGENT,ROLE_AGENT_DOSSIER,ROLE_AGENT_VALIDATEUR', 'dda38b83-bca4-4a25-8e2a-d2eb4947f02d', '0401', 'MJ', true, now() - interval '1 day'),
    ('validateur2@mailou.org', 'Validateur', 'Deux', 'ROLE_AGENT,ROLE_AGENT_DOSSIER,ROLE_AGENT_VALIDATEUR', '98f8f466-e22f-4b51-b776-1271d38922ae', '0402', 'MJ', true, now() - interval '1 day'),
    ('policier1@mailou.org', 'Policier', 'Un', 'ROLE_AGENT,ROLE_AGENT_FORCES_DE_L_ORDRE', 'c8c2929b-29e5-4668-964e-da6f83bda44b', '0601', 'PP', true, now() - interval '1 day'),
    ('policier2@mailou.org', 'Policier', 'Deux', 'ROLE_AGENT,ROLE_AGENT_FORCES_DE_L_ORDRE', 'e316bde4-3d37-4ed8-9001-c40d9b07fe06', '0602', 'PN', true, now() - interval '1 day'),
    ('gendarme1@mailou.org', 'Gendarme', 'Un', 'ROLE_AGENT,ROLE_AGENT_FORCES_DE_L_ORDRE', '51356418-a495-4dda-8fb0-55800c36fb2c', '0701', 'GN', true, now() - interval '1 day'),
    ('gendarme2@mailou.org', 'Gendarme', 'Deux', 'ROLE_AGENT,ROLE_AGENT_FORCES_DE_L_ORDRE', '69e7de5b-0b31-4157-8b76-9328b26222ad', '0702', 'GN', true, now() - interval '1 day')
EOF
fi

frankenphp php-server -r /app/public -a /app/public/index.php
