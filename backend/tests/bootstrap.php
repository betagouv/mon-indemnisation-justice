<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv') && is_file($dotenvFile = __DIR__ . '/../.env.test')) {
    new Dotenv()->bootEnv($dotenvFile);
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
