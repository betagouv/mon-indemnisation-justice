<?php

namespace MonIndemnisationJustice\Entity\Metadonnees;

use MonIndemnisationJustice\Entity\Civilite;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['agent:detail'])]
readonly class InfosRequerant extends AbstractMetadonnees
{
    public function __construct(
        public Civilite $civilite,
        public string $nom,
        public string $prenom,
        public string $telephone,
        public string $courriel,
        public string $message
    ) {}

    protected function normaliser(): array
    {
        return [
            'civilite' => $this->civilite->name,
        ];
    }
}
