<?php

use MonIndemnisationJustice\Kernel;

// Si la variable d'environnement MAINTENANCE est définie et est _truey_, alors on jette une page vide en 503.
if (getenv('MAINTENANCE')) {
    http_response_code(503);

    // TODO envelopper dans une vraie page
    return;
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
