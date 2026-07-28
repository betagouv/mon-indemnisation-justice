<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\Agent;

use Symfony\Component\Validator\Constraints as Assert;

readonly class AttribuerEtablissementInput
{
    public function __construct(
        #[Assert\Expression(
            'this.estExempt == true || value != null',
            message: "L'établissement doit être renseigné",
        )]
        public ?string $etablissement = null,
        #[Assert\Expression(
            'this.estExempt == true || value != null',
            message: "La date d'attribution doit être renseignée",
        )]
        public ?\DateTime $dateAffectation = null,
        public bool $estExempt = false,
    ) {
    }
}
