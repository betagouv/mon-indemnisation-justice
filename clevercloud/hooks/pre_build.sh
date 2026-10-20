#!/bin/bash

# Ce script est lancé avant l'installation des dépendances, i.e. `CC_PRE_BUILD_HOOK` sur CleverCloud
# Doc : https://www.clever.cloud/developers/doc/develop/common-configuration/environment-variables/reference/#deployment-hooks

# ROOT fait référence à la racine du dépôt
ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/../.. && pwd)

echo "Préparation de l'environnement ${APP_ENV}";

# 1. Téléchargement des dépendances Node pour le project backend (copie également le DSFR vers le dossier public)
cd "${ROOT}/backend"

yarn install

# 2. Installation de Firefox via `puppeteer`
npx puppeteer browsers install firefox

# 3. Génération des assets frontend
cd "${ROOT}/frontend"

yarn install --prod --frozen-lockfile
yarn build --outDir="${ROOT}/backend/public/build" --base=/build

# 4. Préparation du script de lancement
if [ -f "${ROOT}/clevercloud/demarrage_${APP_ENV}.sh" ]; then
  cp "${ROOT}/clevercloud/demarrage_${APP_ENV}.sh" "${ROOT}/clevercloud/hooks/pre_run.sh"
  chmod +x "${ROOT}/clevercloud/hooks/pre_run.sh"
fi