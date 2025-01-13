# Manipulations diverses

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