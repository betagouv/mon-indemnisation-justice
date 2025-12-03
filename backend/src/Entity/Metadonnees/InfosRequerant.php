<?php

namespace MonIndemnisationJustice\Entity\Metadonnees;

use MonIndemnisationJustice\Entity\Civilite;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['agent:detail'])]
readonly class InfosRequerant extends AbstractMetadonnees
{
    public function __construct(
        public ?Civilite $civilite,
        public string $nom,
        public string $prenom,
        public string $telephone,
        public string $courriel,
    ) {}

    public static function depuisArray(array $valeurs): static
    {
        $civilite = ($c = $valeurs['civilite'] ?? null) instanceof Civilite ? $c : Civilite::tryFrom($c ?? '');

        return new self(...self::construireArguments(array_merge($valeurs, ['civilite' => $civilite])));
    }
}
