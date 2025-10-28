<?php

namespace MonIndemnisationJustice\Entity\Metadonnees;

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
