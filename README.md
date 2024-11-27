# 🇫🇷 👩‍⚖️ 🤝 Précontentieux 

## Installation

### Image Docker pour PHP, composer, Apache et autres utilitaires

L'image PHP de base contient en réalité un peu plus que PHP:
- `php-fpm` (_Fork Process Manager): qui permet de démarrer autant de processus PHP qu'il y a de requêtes entrantes (c'est l'implémentation [CGI](https://fr.wikipedia.org/wiki/Common_Gateway_Interface) pour PHP)
- les extensions requises pour PHP: `zip`, `intl`, `calendar`, `gd`, `pgsql`, `pdo`, `pdo_pgsql` et `ldap`
- `node`: afin de compiler les assets (grâce à [`vite`](https://vite.dev/))
- `firefox-esr` & `chromium`: afin de disposer de navigateurs _headless_ pour les tests fonctionnels
- `wkhtmltopdf`: qui, comme son nom l'indique, permet de convertir un fichier HTML en PDF 
- `libreoffice`: afin de pouvoir, un jour, convertir un fichier `.docx` en PDF

Cette image est utilisée pour le développement, i.e. _en local_, ainsi que pour jouer les tests unitaires en CI (Gitlab).

Hormis Apache `httpd` qui n'est pas utilisé en développement, le reste de la configuration vise à être alignée avec [la
configuration des serveurs sur Clever CLoud](https://developers.clever-cloud.com/doc/applications/php/).

Puisque l'image est longue à builder, on ne la génère qu'en cas de modification type montée de version de PHP. Le reste
du temps, on s'appuie sur l'image précédemment construite et publiée (actuellement sur [le compte `pierrelemee` de Docker
Hub](https://hub.docker.com/repository/docker/pierrelemee/precontentieux-full/general)).

Pour la construire :

```bash
docker build -f .docker/php/Dockerfile . --platform linux/amd64 -t pierrelemee/precontentieux-full:latest
```

Pour la publier :

```bash
docker push pierrelemee/precontentieux-full:latest
```


### Faire tourner l'environnement de développement depuis Docker

Avant tout, éditez un fichier `.env.local` en reprenant les valeurs déclarées dans `.env.internet`.

Ensuite, démarrer le projet via `docker compose`:


```bash
docker compose build
docker compose up -d
```

En développement, lancez [le mode `watch`](https://docs.docker.com/compose/file-watch/):

```bash
docker compose watch
```

Pour exécuter une commande, exemple ici `cat composer.json`, sur le conteneur du service `symfony`, lancez:

```bash
docker compose exec symfony cat composer.json
```

Pour sauter directement sur le conteneur, ouvrez un shell comme suit:

```bash
docker compose exec symfony bash
```

### Données de test

Pour pouvoir naviguer sur le site, il va vous falloir créer des utilisateurs avec leurs données. On peut réaliser ceci
grâce aux _data fixtures_:

```bash
# Depuis le conteneur du service `symfony`:
bin/console doctrine:fixture:load --purge-with-truncate --no-interaction
```

## Restaurer un dump

Nous allons produire un fichier au format SQL qui sera utilisé directement par le conteneur Docker de PostgreSQL en
local.

Commençons par nettoyer le dossier source:

```bash
rm -f .docker/scripts/*
```

Il faut d'abord générer un dump au format SQL. Pour cela, jouer cette commande depuis la base existante:

```bash


```bash
pg_dump $DATABASE_URL --schema=public --if-exists --clean --no-owner --no-privileges --exclude-table=public.spatial_ref_sys | grep -i -v 'extensions' > ./.docker/postgres/scripts/001-precontentieux-prod-$(date +'%Y-%m-%d').sql
```

On note ici que pour éliminer le bruit autour des extensions, on filtre à la volée les clauses qui les concernent.

Il reste à redémarrer le conteneur docker pour PostgreSQL :

```bash
docker compose rm --stop --force postgres
# Suppression de données de Postgre, pour forcer la recréation et la lecture des fichiers d'entrée
rm -Rf .docker/postgres/data/*
docker compose up -d postgres
```

## Re-générer le manifeste des routes pour le javascript

Quand une nouvelle route est exposée en JS (via l'attribut `options: ['expose' => true]`), ou quand son nom est changé,
il faut re-générer le fichier de manifeste des routes Symfony exposées au JS. 

Ceci se fait via la commande `fos:js-routing:dump`, soit nativement :

```bash
bin/console fos:js-routing:dump --format json
```

Le fichier est alors créé sous `public/js/fos_js_routes.json`.

Soit depuis docker

```bash
docker compose exec symfony bin/console fos:js-routing:dump --format json
docker compose cp symfony:/app/public/js/fos_js_routes.json public/js/fos_js_routes.json
```

## Test fonctionnels:

En cas d'erreur suivante :

```
Fatal error: Uncaught Facebook\WebDriver\Exception\Internal\WebDriverCurlException: Curl error thrown for http DELETE to /session/a0be33fe-07d6-4f9b-8c03-24fc29795ae8

Failed to connect to 127.0.0.1 port 4444 after 0 ms: Couldn't connect to server in /app/vendor/php-webdriver/webdriver/lib/Exception/Internal/WebDriverCurlException.php:20
```

Ensuite suivie de :

```
RuntimeException: The port 9080 is already in use.
```

Il faut prendre soin de supprimer le process zombie qui écoute le port désiré par `panther`:

```bash
lsof -nP -t -i:9080 | xargs kill -9
```
