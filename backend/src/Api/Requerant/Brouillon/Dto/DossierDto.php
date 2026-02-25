<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Api\Agent\FDO\Output\DeclarationFDOBrisPorteOutput;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\QualiteRequerant;
use MonIndemnisationJustice\Entity\TestEligibilite;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: BrisPorte::class)]
class DossierDto
{
    public RequerantDto $requerant;
    #[Map(source: 'qualiteRequerant')]
    public QualiteRequerant $rapportAuLogement;
    public string $descriptionRapportAuLogement;
    public ?TestEligibilite $testEligibilite;
    public ?DeclarationFDOBrisPorte $declarationFDO;
}
