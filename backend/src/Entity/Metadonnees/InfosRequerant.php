<?php

namespace MonIndemnisationJustice\Entity\Metadonnees;

use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['agent:detail'])]
readonly class InfosRequerant
{
    public function __construct(
        public string $nom,
        public string $prenom,
        public string $telephone,
        public string $courriel,
        public string $message
    ) {}
}
