# Images PHP

On a en réalité ici 3 images:
- `Dockerfile`: c'est l'image de _base_. Elle s'appuie sur [FrankenPHP](https://frankenphp.dev/) pour toute la partie
PHP, en version 8.3, mais intègre également `node` en version 22 et `chromium` qui permet l'impression en PDF
- `dev.dockerfile`: reprend l'image de base avec une couche supérieure pour gérer les certificats locaux. Cela permet
notamment à l'application PHP t'intéragir directement en HTTPS avec les conteneurs de ProConnect et FranceConnect
- `prod.dockerfile`: c'est le fichier qui est utilisé pour le déploiement (en `prod` comme en `develop`). Il enrichit la
base du code source (contrairement à l'image de dev où le code ser _monté_ par un volume) ainsi que toutes les
dépendances applicatives, PHP comme JS.


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