# 🇫🇷 👩‍⚖️ 🤝 Mon Indemnisation Justice (ex Précontentieux)

## Installation

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


## Test fonctionnels:

Pour lancer un test fonctionnel, on utilise [Panther](https://github.com/symfony/panther) et Firefox.

Pour pouvoir les lancer depuis le conteneur Docker:

```bash
# Firefox est déjà installé sur l'image de base ✅
./vendor/bin/bdi detect drivers
./vendor/bin/phpunit  tests/Functional/
# Ou un test spécifique, pour un jeu de données précis:
./vendor/bin/phpunit --filter DepotBrisPorteTest::testDepotDossierBrisPorte@desktop
```

Si le test ne passe pas, c'est très compliqué de débugger en _headless_ depuis un conteneur. Dans ce cas on rejoue le
test depuis la machine _host_ :

```bash
# Aliaser les noms de service Docker comme étant locaux:
echo "127.0.0.1 postgres" | sudo tee -a /etc/hosts
echo "127.0.0.1 mailpit" | sudo tee -a /etc/hosts
# Installer `geckodriver` https://github.com/symfony/panther?tab=readme-ov-file#installing-chromedriver-and-geckodriver
# Ex sur MacOS :
brew install geckodriver
PANTHER_NO_HEADLESS=1 ./vendor/bin/phpunit tests/Functional/
```

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
