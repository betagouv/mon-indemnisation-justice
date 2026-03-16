<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\CoordonneesRequerant;

class CoordonneesRequerantOutput
{
    public function __construct(
        public readonly Civilite $civilite,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $telephone,
        public readonly string $courriel,
    ) {
    }

    public static function depuisCoordonneesRequerant(?CoordonneesRequerant $coordonneesRequerant): ?self
    {
        if (null === $coordonneesRequerant) {
            return null;
        }

        return new self(
            civilite: $coordonneesRequerant->getCivilite(),
            nom: $coordonneesRequerant->getNom(),
            prenom: $coordonneesRequerant->getPrenom(),
            telephone: $coordonneesRequerant->getTelephone(),
            courriel: $coordonneesRequerant->getCourriel(),
        );
    }
}
