# Précontentieux

## Installation

```
# création de la BDD
php bin/console d:d:c
php bin/console d:s:u --force
# chargement des référentiels
php bin/console a:a:charger
# lancement du serveur web (si mode développeur)
yarn watch
```

## L'administrateur fonctionnel

L'administrateur fonctionnel est obligatoire pour valider/invalider les nouveaux membres. Seuls un administrateur système peut effectuer l'opération de création.

### Création d'un compte
```
php bin/console a:a:a <admin>
```
