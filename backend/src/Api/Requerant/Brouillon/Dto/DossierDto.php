<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

use MonIndemnisationJustice\Api\Requerant\Brouillon\Mapper\DossierDtoMapper;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RapportAuLogement;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;

// #[Map(source: Dossier::class)]
#[Map(source: Dossier::class, transform: [DossierDtoMapper::class, 'depuisDossier'])]
#[Map(target: Dossier::class, transform: [DossierDtoMapper::class, 'versDossier'])]
class DossierDto
{
    #[Map(if: false)]
    public ?string $reference;
    // public UsagerDto $usager;
    #[Map(if: false)]
    public ?int $usager;
    #[Map(if: false)]
    public ?bool $estPersonneMorale = null;
    public ?PersonnePhysiqueDto $personnePhysique;
    public ?PersonneMoraleDto $personneMorale = null;
    // #[Map(source: 'brisPorte.rapportAuLogement')]
    // #[Map(target: 'brisPorte.rapportAuLogement')]
    public RapportAuLogement $rapportAuLogement;
    // #[Map(source: 'brisPorte.descriptionRapportAuLogement')]
    // #[Map(target: 'brisPorte.descriptionRapportAuLogement', if: new TargetClass(Dossier::class))]
    public ?string $descriptionRapportAuLogement = null;
    #[Map(if: false)]
    public ?AdresseDto $adresse;

    // #[Map(source: 'brisPorte.dateOperation')]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public ?\DateTimeImmutable $dateOperation;

    public array $etatActuel = [];

    public ?int $idTestEligibilite;

    public ?Uuid $idDeclarationFDO;
    public ?bool $estPorteBlindee;
    public ?string $description;
    /** @var PieceJointeDto[] */
    public array $piecesJointes;
}
