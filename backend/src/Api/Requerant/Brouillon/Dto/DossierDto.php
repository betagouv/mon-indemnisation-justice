<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\QualiteRequerant;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: BrisPorte::class)]
#[Map(target: BrisPorte::class)]
class DossierDto
{
    public string $reference;
    public RequerantDto|AdresseDto $requerant;
    #[Map(source: 'qualiteRequerant')]
    public QualiteRequerant $rapportAuLogement;
    public string $descriptionRapportAuLogement;
    //public ?TestEligibilite $testEligibilite;
    //public ?DeclarationFDOBrisPorte $declarationFDO;
}
