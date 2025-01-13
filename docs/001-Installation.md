# Installation

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