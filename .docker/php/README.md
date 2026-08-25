# Images PHP

On a en réalité ici 4 images :
- `.docker/php/Dockerfile`: c'est l'image de _base_. Elle s'appuie sur [FrankenPHP](https://frankenphp.dev/) pour toute la partie
PHP, en version 8.5, mais intègre également `node` en version 22 et `chromium` qui permet l'impression en PDF
- `.docker/php/dev.dockerfile`: reprend l'image de base avec une couche supérieure pour gérer les certificats locaux. Cela permet
notamment à l'application PHP t'intéragir directement en HTTPS avec les conteneurs de ProConnect et FranceConnect
- `.docker/prod/web.dockerfile`: c'est le fichier qui est utilisé pour le déploiement (en `prod` comme en `develop`). Il enrichit la
base du code source (contrairement à l'image de dev où le code ser _monté_ par un volume) ainsi que toutes les
dépendances applicatives, PHP comme JS.
- `.docker/prod/worker.dockerfile`: quasiment la même image que `.docker/prod/web.dockerfile` mais sans web, joue
uniquement les processus de worker via Supervisor avec une API de monitoring par dessus.


### Comment la _builder_ ?

Via `buildx`, il faut d'abord créer le _builder_:

```bash
docker buildx create --use --name multi --append
```

```bash
docker buildx build --platform linux/amd64,linux/arm64 . -f .docker/php/Dockerfile -t pierrelemee/mij-frankenphp
```

Puis un simple push du tag.

L'image est également construite et publiée à chaque commit sur la branche `main` en CI (avec du cache).