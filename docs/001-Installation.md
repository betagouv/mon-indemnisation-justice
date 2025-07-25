# Installation


## Génération des certificats HTTPS

Nous allons avoir besoin d'accéder au site via HTTPS, et ce même en développement. En effet, l'authentification se fait
via ProConnect pour les agents ou FranceConnect pour les requérants. Aussi l'échange de données doit se faire de manière
sécurisée.

En local, on va utiliser l'outil `mkcert` qui permet de générer des **certificats locaux** (i.e. "self trusted').

Pour cela, jouer les commandes suivantes, _avant_ de démarrer les conteneurs Docker (les certificats sont requis pour
construire l'image du server web `nginx`):

```bash
cd .docker/nginx/ssl

# Optionnel, si le certificat racine (i.e. le fichier `rootCA.pem` n'est pas encore présent)
CAROOT=$(pwd) mkcert -install
# Sous MacOSX, déclaré le certificat fiable
sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain "$(pwd)/rootCA.pem"

for domain in mon-indemnisation.anje-justice.dev mon-indemnisation.justice.gouv.dev mon-indemnisation.justice.gouv.test proconnect.gouv.test franceconnect.gouv.test; do
  # Génération
  CAROOT=$(pwd) mkcert "${domain}"
  # Register domain as local domain
  echo "127.0.0.1 ${domain}" | sudo tee -a /etc/hosts
done
````

Avant tout, éditez un fichier `.env.dev` en reprenant les valeurs déclarées dans `.env.dist`.

Ensuite, démarrer le projet via `docker compose`:


```bash
docker compose build
docker compose up -d
```

Comme l'image de base de `symfony` peut être amenée à évoluer (elle est construite en CI par
[le workflow `build-docker-image.yml`](../.github/workflows/build-docker-image.yml)), il est nécessaire de reconstruire
le conteneur régulièrement:

```bash
docker compose build --pull symfony 
docker compose up -d --force-recreate symfony 
```

Pour exécuter une commande, exemple ici `cat composer.json`, sur le conteneur du service `symfony`, lancez :

```bash
docker compose exec symfony cat composer.json
```

Pour sauter directement sur le conteneur, ouvrez un shell comme suit:

```bash
docker compose exec symfony bash
```

### Données de test

Pour pouvoir naviguer sur le site, il va vous falloir créer des utilisateurs avec leurs données. On peut réaliser ceci
grâce aux _data fixtures_ :

```bash
# Depuis le conteneur du service `symfony`:
bin/console doctrine:fixture:load --purge-with-truncate --no-interaction
```