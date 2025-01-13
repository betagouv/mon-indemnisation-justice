# Tests

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
