<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Dto;

use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\EtatDeclarationFDOBrisPorte;

class EtatDeclarationDto
{
    public function __construct(
        public readonly EtatDeclarationFDOBrisPorte $etat,
        public readonly \DateTimeImmutable $date,
    ) {
    }

    public static function depuisDeclaration(DeclarationFDOBrisPorte $declaration): EtatDeclarationDto
    {
        $etatDeclaration = EtatDeclarationFDOBrisPorte::depuisDeclaration($declaration);

        return new EtatDeclarationDto($etatDeclaration, $etatDeclaration->getDate($declaration));
    }
}
