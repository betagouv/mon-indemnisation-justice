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
* `CC_PRE_BUILD_HOOK` : avant d'installer les dépendances Composer, joue [le script `clevercloud/hooks/pre_build.sh`](../clever/hooks/pre_build.sh)
qui
* 1. installe les dépendances Node et génère les assets frontend
* 2. installe `firefox` en version _headless_ nécessaire à l'impression des documents en PDF via `puppeteer`. Pour garder
le binaire téléchargé dans le cache du conteneur qui sera construit, on définit `PUPPETEER_CACHE_DIR` à `$ROOT/.puppeteer`
* 3. prépare le script de lancement spécifique à l'environnement, si défini dans `clevercloud/scripts`, ce dossier étant, par
sécurité, exclu du cache de CleverCloud pour ne pas risquer d'être exécuté à tort (via la valeur `clevercloud/scripts` pour
`CC_IGNORE_FROM_BUILDCACHE`)
* `CC_PRE_RUN_HOOK` : pointe vers le script `clevercloud/hooks/pre_run.sh` préparé à l'étape précédente
* `CC_WEBROOT` : informe FrankenPHP que le point d'entrée se situe sous `/backend/public`
* `CC_RUN_COMMAND` : précise comment est configuré FrankenPHP
* `CC_WORKER_COMMAND` : lance le worker Symfony Messenger, relancé automatiquement par CleverCloud
(`php bin/console messenger:consume async --time-limit=3600 --memory-limit=256M`)
* `MERCURE_JWT_SECRET`, `MERCURE_PUBLISHER_JWT_KEY` et `MERCURE_SUBSCRIBER_JWT_KEY` : une même clé aléatoire d'au
moins 32 caractères (`openssl rand -hex 32`), propre à chaque environnement, qui signe les JWT du hub Mercure activé
dans le `Caddyfile`. Sans elles, le hub démarre mais refuse toute publication et tout abonnement
* `MERCURE_URL` : URL interne du hub, utilisée pour publier (`http://localhost:8080/.well-known/mercure`)
* `MERCURE_PUBLIC_URL` : URL publique du hub (`https://<domaine>/.well-known/mercure`), qui doit être sur le domaine
du site pour que le cookie d'abonnement soit transmis

#### Diffusion en direct des dossiers (Mercure)

Le requérant reçoit en direct les changements de ses dossiers, par exemple la validation de la signature électronique
de la déclaration d'acceptation :

* chaque dossier a son topic Mercure, `requerant/dossier/{id}` (cf. `DiffuseurDossierRequerant::topic()`) ;
* à chaque consultation d'un dossier (`GET /api/requerant/dossier/bris-de-porte/{reference}`), l'API pose le cookie
  `mercureAuthorization` : un JWT qui autorise l'abonnement aux topics de **tous** les dossiers de l'usager. Comme il
  n'existe qu'un cookie par hub, un dossier ouvert dans un autre onglet garde ainsi son autorisation ;
* les mises à jour sont publiées par le worker (`DiffuseurDossierRequerant::diffuserDossier()`), en privé : seul un
  abonné dont le JWT liste le topic les reçoit. L'identifiant (entier) du dossier peut donc figurer dans le topic sans
  risque ;
* côté frontend, le hook `useAbonnementMercure` (`frontend/src/common/hooks/`) s'abonne au topic et remplace le
  dossier affiché par celui reçu.

> [!WARNING]
> Le hub Mercure intégré à FrankenPHP conserve ses évènements localement à chaque instance : avec plusieurs instances,
> une mise à jour publiée par le worker d'une instance n'atteint pas les navigateurs connectés à une autre.

> [!NOTE]  
> Le déploiement s'appuie [l'application CLI `clever-tools`](https://github.com/CleverCloud/clever-tools) qui pourrait de
fait être utilisée directement par un développeur habilité

Le déploiement sur le serveur de test déclenche une "remise à zéro" de la base de données pour prévenir un éventuel
conflit entre deux branches amenant chacune sa version modifiée du schéma. Les migrations sont donc jouées sur une base
vide pour s'assurer que le code est alignée avec le schéma de données.

