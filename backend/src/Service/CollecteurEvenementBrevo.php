<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\EnvoiCourrielEvenement;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Collecte des évènements d'email depuis Brevo.
 */
class CollecteurEvenementBrevo
{
    public function __construct(
        #[Autowire(env: 'default::BREVO_TOKEN_VERIFICATION')]
        protected readonly ?string $tokenVerification = null,
    ) {

    }

    public function estActif(): bool
    {
        return null !== $this->tokenVerification;
    }

    public function collecter(Request $request): ?EnvoiCourrielEvenement
    {
        // TODO tenter de récupérer un `EnvoiCourriel` par son id via `X-Mailin-Custom` https://developers.brevo.com/docs/transactional-webhooks#sent
        return null;
    }
}
