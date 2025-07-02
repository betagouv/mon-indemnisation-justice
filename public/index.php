<?php

use MonIndemnisationJustice\Kernel;

// Si la variable d'environnement MIJ_DOMAINE_PRIMAIRE est définie et que le domaine actuellement demandé ne correspond
// pas, on redirige vers le domaine canonique.
if (false !== ($domainePrimaire = getenv('MIJ_DOMAINE_PRIMAIRE')) && $_SERVER['HTTP_HOST'] !== $domainePrimaire) {
    header("Location: https://$domainePrimaire$_SERVER[REQUEST_URI]");
    exit;
}

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
