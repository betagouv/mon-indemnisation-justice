# CI et déploiement

## Intégration continue (CI)

Chaque commit poussé déclenche l'exécution du [workflow `test.yml`](../.github/workflows/test.yml), à l'exception de ceux
ajoutés aux branches de déploiement `prod` ou `develop`. Les tests jouent simultanément les tests frontend, backend et
fonctionnels.

Chaque commit sur `main` qui contient des modifications sur [le Dockerfile racine](.docker/php/Dockerfile) déclence le [workflow `build-docker-image.yml`](../.github/workflows/build-docker-image.yml)
qui construit et pousse [l'image sur le hub de Docker](https://hub.docker.com/repository/docker/pierrelemee/mij-frankenphp/general).

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

Que ce soit `prod` ou `develop`, chaque environnement déploie simultanément 2 applications sur le cloud :
* une app **web** : le serveur Symfony, via FrankenPHP, qui sert également l'application React
* une app **worker** qui joue les tâches de fond ainsi que les jobs CRON. Elle ne contient que l'application Symfony

> [!NOTE]  
> Le déploiement s'appuie [l'application CLI `clever-tools`](https://github.com/CleverCloud/clever-tools) qui pourrait de
fait être utilisée directement par un développeur habilité

Le déploiement sur le serveur de test déclenche une "remise à zéro" de la base de données pour prévenir un éventuel
conflit entre deux branches amenant chacune sa version modifiée du schéma. Les migrations sont donc jouées sur une base
vide pour s'assurer que le code est alignée avec le schéma de données. 

