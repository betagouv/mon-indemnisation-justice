<?php

namespace MonIndemnisationJustice\Routing;

use MonIndemnisationJustice\Service\CollecteurEvenementBrevo;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;

#[AsRoutingConditionService]
class VerificateurFonctionnaliteActive
{
    public const string FONCTIONNALITE_BREVO = 'brevo';

    public function __construct(
        protected readonly CollecteurEvenementBrevo $collecteurEvenementBrevo,
    ) {

    }

    public function estActive(string $fonctionnalite): bool
    {
        return match ($fonctionnalite) {
            self::FONCTIONNALITE_BREVO => $this->collecteurEvenementBrevo->estActif(),
            default => false,
        };
    }
}
