<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RapportAuLogement;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;

class DossierDto
{
    public function __construct(
        public ?string $reference,
        public int $usager,
        public ?EtatDossierDto $etatActuel = null,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public ?\DateTimeImmutable $dateDepot = null,
        public ?bool $estPersonneMorale = null,
        public ?PersonnePhysiqueDto $personnePhysique = null,
        public ?PersonneMoraleDto $personneMorale = null,
        public ?RapportAuLogement $rapportAuLogement = null,
        public ?string $descriptionRapportAuLogement = null,
        public ?AdresseDto $adresse = null,
        // #[Map(source: 'brisPorte.dateOperation'),
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public ?\DateTimeImmutable $dateOperation = null,
        public ?int $idTestEligibilite = null,
        public ?Uuid $idDeclarationFDO = null,
        public bool $estPorteBlindee = false,
        public ?string $description = null,
        /** @var PieceJointeDto[] */
        public array $piecesJointes = [],
    ) {
    }

    public function versDossier(Dossier $dossier): Dossier
    {
        return $dossier->setRequerant(
            $this->personneMorale ?
                $this->personneMorale?->versPersonneMorale($dossier?->getRequerantPersonneMorale()) :
                $this->personnePhysique->versPersonnePhysique($dossier?->getRequerantPersonnePhysique())
        )
            ->setBrisPorte(
                $dossier->getBrisPorte()
                    ->setRapportAuLogement($this->rapportAuLogement)
                    ->setPrecisionRapportAuLogement($this->descriptionRapportAuLogement)
                    ->setPorteBlindee($this->estPorteBlindee)
                    ->setDescriptionRequerant($this->description)
                    ->setDateOperation($this->dateOperation ? \DateTime::createFromInterface($this->dateOperation) : null)
                    ->setAdresse($this->adresse?->versAdresse($dossier->getBrisPorte()->getAdresse()))
            );
    }

    public static function depuisDossier(Dossier $dossier): self
    {
        return new self(
            reference: strval($dossier->getId()),
            usager: $dossier->getUsager()->getId(),
            etatActuel: EtatDossierDto::depuisEtatDossier($dossier->getEtatDossier()),
            dateDepot: $dossier->getDateDepot() ? \DateTimeImmutable::createFromInterface($dossier->getDateDepot()) : null,
            estPersonneMorale: $dossier->estPersonneMorale(),
            personnePhysique: PersonnePhysiqueDto::depuisPersonnePhysique($dossier->getRequerantPersonnePhysique()),
            personneMorale: PersonneMoraleDto::depuisPersonneMorale($dossier->getRequerantPersonneMorale()),
            rapportAuLogement: $dossier->getBrisPorte()->getRapportAuLogement(),
            descriptionRapportAuLogement: $dossier->getBrisPorte()->getPrecisionRapportAuLogement(),
            adresse: AdresseDto::depuisAdresse($dossier->getBrisPorte()->getAdresse()),
            dateOperation: $dossier->getBrisPorte()->getDateOperation() ? \DateTimeImmutable::createFromInterface($dossier->getBrisPorte()->getDateOperation()) : null,
            idTestEligibilite: $dossier->getBrisPorte()->getTestEligibilite()?->id,
            idDeclarationFDO: $dossier->getBrisPorte()->getDeclarationFDO()?->getId(),
            estPorteBlindee: $dossier->getBrisPorte()?->estPorteBlindee(),
            description: $dossier->getBrisPorte()->getDescriptionRequerant(),
            // TODO gérer ça plus tard
            piecesJointes: $dossier->getPiecesJointes()->map(fn (Document $pieceJointe) => PieceJointeDto::depuisDocument($pieceJointe))->toArray(),
        );
    }
}
