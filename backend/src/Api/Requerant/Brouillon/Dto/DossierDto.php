<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RapportAuLogement;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Uid\Uuid;

#[Map(source: Dossier::class)]
#[Map(target: Dossier::class)]
class DossierDto
{
    public ?string $reference;
    public UsagerDto $usager;
    public PersonnePhysiqueDto|PersonneMoraleDto $requerant;
    #[Map(source: 'brisPorte.qualiteRequerant')]
    public RapportAuLogement $rapportAuLogement;
    #[Map(source: 'brisPorte.descriptionRapportAuLogement')]
    public string $descriptionRapportAuLogement;
    public ?AdresseDto $adresse;

    #[Map(source: 'brisPorte.dateOperation')]
    public ?\DateTimeImmutable $dateOperation;
    #[Ignore]
    public ?int $idTestEligibilite;
    #[Ignore]
    public ?Uuid $idDeclarationFDO;
}
