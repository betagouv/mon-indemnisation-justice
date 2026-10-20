# CI et déploiement

## Intégration continue (CI)

Chaque commit poussé déclenche l'exécution du [workflow `test.yml`](../.github/workflows/test.yml), à l'exception de ceux
ajoutés aux branches de déploiement `prod` ou `develop`. Les tests jouent simultanément les tests frontend, backend et
fonctionnels.

Chaque commit sur `main` pour lesquels les tests sont ✅ déclenche le [workflow `merge-to-prod.yml`](../.github/workflows/merge-to-prod.yml)
qui merge la branche `main` dans `prod`. Ce workflow est conditionné à un "déploiement" au sens de Github, qui requiert
la validation par un développeur du projet. Ce mécanisme nous permet de garder la main pour déclencher le déploiement au
moment voulu.

Le [workflow `deploy-prod.yml`](../.github/workflows/deploy-prod.yml) s'exécute à chaque commit poussé sur `prod`, branche
qui est donc passive puisqu'elle ne reçoit que les mises à jour de `main`.

> [!NOTE]  
> On peut toujours déclencher un déploiement en poussant directement sur `prod`, mais ceci aurait pour effet de bloquer la
prochaine exécution du [workflow `merge-to-prod.yml`](../.github/workflows/merge-to-prod.yml) qui rencontrerait un conflit.

Le [workflow `deploy-develop.yml`](../.github/workflows/deploy-develop.yml) déploie l'environnement de dev à partir de `develop`.
Cette branche est, elle, utilisée comme un pointeur pour permettre aux développeurs, via un "hard reset", de choisir quelle
branche de développement doit être mise à disposition des tests.

## Déploiement

Chaque environnement est lié à une application de [type FrankenPHP](https://www.clever.cloud/developers/doc/deploy/applications/frankenphp).
Celui-ci est configuré de la manière suivante :
* `APP_FOLDER` : pointe sur le sous dossier `backend` du dépot, où se trouve le fichier `composer.json` requis pour installer les dépendances backend
* `CC_PHP_DEV_DEPENDENCIES` : défini _uniquement_ sur le site de recette avec `install` pour installer les dépendances de
développement permettant de jouer les fixtures de test
* `CC_PHP_VERSION` : PHP tourne en  version `8.5`
* `CC_PRE_BUILD_HOOK` : avant d'installer les dépendances Compposer, joue [le script `clever/hooks/pre_build.sh`](../clever/hooks/pre_build.sh)
qui
* 1. installe les dépendances Node et génère les assets frontend
* 2. installe `firefox` en version _headless_ nécessaire à l'impression des documents en PDF via `puppeteer`
* 3. prépare le script de lancement spécifique à l'environnement, si défini dans `clever/scripts`, ce dossier étant, par
sécurité, exclu du cache de CleverCloud pour ne pas risquer d'être exécuté à tort  
* `CC_PRE_RUN_HOOK` : pointe vers le script `clever/hooks/pre_run.sh` préparé à l'étape précédente
* `CC_WEBROOT` : informe FrankenPHP que le point d'entrée se situe sous `/backend/public`


> [!NOTE]  
> Le déploiement s'appuie [l'application CLI `clever-tools`](https://github.com/CleverCloud/clever-tools) qui pourrait de
fait être utilisée directement par un développeur habilité

Le déploiement sur le serveur de test déclenche une "remise à zéro" de la base de données pour prévenir un éventuel
conflit entre deux branches amenant chacune sa version modifiée du schéma. Les migrations sont donc jouées sur une base
vide pour s'assurer que le code est alignée avec le schéma de données.

