# Manipulations diverses

## Restaurer un dump


Nous allons produire un fichier au format SQL qui sera utilisé directement par le conteneur Docker de PostgreSQL en
local.

Commençons par nettoyer le dossier source :

```bash
rm -f .docker/scripts/*.sql
```

Il faut d'abord générer un dump au format SQL. Pour cela, jouer cette commande depuis la base existante:

```bash


```bash
pg_dump $DATABASE_URL --schema=public --if-exists --clean --no-owner --no-privileges --exclude-table=public.spatial_ref_sys | grep -i -v 'extensions' | sed -n '/^CREATE SEQUENCE public.adresse_id_seq$/,$p' > ./.docker/postgres/scripts/001-precontentieux-prod-$(date +'%Y-%m-%d').sql
```

On note ici que pour éliminer le bruit autour des extensions, on filtre à la volée les clauses qui les concernent.

Pareillement, on élimine toutes les requêtes préliminaires, de type `DROP table/sequence`, qui sont superflues ici
puisqu'en Docker on peut supprimer le conteneur et le recréer _à vide_ à notre guise. 

Il reste à redémarrer le conteneur docker pour PostgreSQL :

```bash
docker compose rm --stop --force postgres
# Suppression de données de Postgre, pour forcer la recréation et la lecture des fichiers d'entrée
rm -Rf .docker/postgres/data/*
docker compose up -d postgres
```