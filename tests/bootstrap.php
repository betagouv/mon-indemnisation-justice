<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (class_exists(Dotenv::class) && is_file($dotenvFile = __DIR__.'/../.env.test')) {
    (new Dotenv())->load($dotenvFile);
}

// Lancer la commande de génération des données de test :
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" doctrine:fixture:load --purge-with-truncate -n -vvv',
    $_ENV['APP_ENV'],
    __DIR__
));
