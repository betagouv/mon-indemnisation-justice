<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\QualiteRequerant;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Dossier::class)]
#[Map(target: Dossier::class)]
class DossierDto
{
    public string $reference;
    public UsagerDto|AdresseDto $requerant;
    #[Map(source: 'qualiteRequerant')]
    public QualiteRequerant $rapportAuLogement;
    public string $descriptionRapportAuLogement;
    // public ?TestEligibilite $testEligibilite;
    // public ?DeclarationFDOBrisPorte $declarationFDO;
}
