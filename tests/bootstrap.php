<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (class_exists(Dotenv::class) && is_file($dotenvFile = __DIR__.'/../.env.test')) {
    (new Dotenv())->load($dotenvFile);
}
