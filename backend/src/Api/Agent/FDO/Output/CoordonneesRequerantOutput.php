<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\CoordonneesRequerant;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: CoordonneesRequerant::class)]
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

    public static function depuisCoordonneesRequerant(CoordonneesRequerant $coordonneesRequerant): self
    {
        return new self(
            civilite: $coordonneesRequerant->getCivilite(),
            nom: $coordonneesRequerant->getNom(),
            prenom: $coordonneesRequerant->getPrenom(),
            telephone: $coordonneesRequerant->getTelephone(),
            courriel: $coordonneesRequerant->getCourriel(),
        );
    }
}
