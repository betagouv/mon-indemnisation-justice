<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RapportAuLogement;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(source: Dossier::class)]
#[Map(target: Dossier::class)]
class DossierDto
{
    public ?string $reference;
    // public UsagerDto $usager;
    public ?int $usager;
    #[Map(if: false)]
    public ?bool $estPersonneMorale;
    public PersonnePhysiqueDto $personnePhysique;
    public PersonneMoraleDto $personneMorale;
    #[Map(source: 'brisPorte.rapportAuLogement')]
    public RapportAuLogement $rapportAuLogement;
    #[Map(source: 'brisPorte.descriptionRapportAuLogement')]
    public string $descriptionRapportAuLogement;
    public ?AdresseDto $adresse;

    #[Map(source: 'brisPorte.dateOperation')]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Assert\Date]
    public ?\DateTimeImmutable $dateOperation;

    public array $etatActuel = [];

    public ?int $idTestEligibilite;

    public ?Uuid $idDeclarationFDO;
    public ?bool $estPorteBlindee;
    public ?string $description;
    /** @var PieceJointeDto[] */
    public array $piecesJointes;
}
