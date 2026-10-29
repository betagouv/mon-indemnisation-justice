<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Dto;

use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use MonIndemnisationJustice\Entity\Document;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Déclaration de bris de porte, telle qu'échangée avec le frontend de l'agent des FDO.
 */
class DeclarationFDOBrisPorteDto
{
    // Les champs `readonly` sont exclus de la dénormalisation (erreur s'ils sont renseignés)
    public readonly Uuid $id;
    public readonly ?string $reference;
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Assert\NotNull(message: "La date de l'opération ayant conduit au bris de porte est requise")]
    public ?\DateTimeImmutable $dateOperation = null;
    #[Assert\NotNull(message: "Veuillez-nous indiquer s'il s'agit d'une erreur")]
    public ?DeclarationFDOBrisPorteErreurType $estErreur = null;
    public ?string $descriptionErreur = null;
    #[Assert\NotNull(message: "L'adresse du bris de porte est requise")]
    #[Assert\Valid]
    public ?AdresseDto $adresse = null;
    #[Assert\NotNull(message: 'La procédure judiciaire est requise')]
    #[Assert\Valid]
    public ?ProcedureJudiciaireDto $procedure = null;
    public ?string $precisionsRequerant = null;
    #[Assert\Valid]
    public ?CoordonneesRequerantDto $coordonneesRequerant = null;
    public readonly AgentDto $agent;
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::W3C])]
    public readonly \DateTimeImmutable $dateCreation;
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::W3C])]
    public readonly ?\DateTimeImmutable $dateSoumission;
    /** @var DocumentDto[] */
    public readonly array $piecesJointes;

    public static function depuisDeclaration(DeclarationFDOBrisPorte $declaration): self
    {
        $dto = new self();
        $dto->id = $declaration->getId();
        $dto->reference = $declaration->getReference();
        $dto->dateOperation = $declaration->getDateOperation();
        $dto->estErreur = $declaration->getEstErreur();
        $dto->descriptionErreur = $declaration->getDescriptionErreur();
        $dto->adresse = $declaration->getAdresse() ? AdresseDto::depuisAdresse($declaration->getAdresse()) : null;
        $dto->procedure = $declaration->getProcedure() ? ProcedureJudiciaireDto::depuisProcedureJudiciaire($declaration->getProcedure()) : null;
        $dto->precisionsRequerant = $declaration->getPrecisionsRequerant();
        $dto->coordonneesRequerant = $declaration->getCoordonneesRequerant() ? CoordonneesRequerantDto::depuisCoordonneesRequerant($declaration->getCoordonneesRequerant()) : null;
        $dto->agent = AgentDto::depuisAgent($declaration->getAgent());
        $dto->dateCreation = $declaration->getDateCreation();
        $dto->dateSoumission = $declaration->getDateSoumission();
        $dto->piecesJointes = $declaration->getPiecesJointes()->map(fn (Document $pieceJointe) => DocumentDto::depuisDocument($pieceJointe))->getValues();

        return $dto;
    }

    /**
     * Reporte les valeurs éditables du DTO sur la déclaration (et ses entités liées) fournie.
     */
    public function versDeclaration(DeclarationFDOBrisPorte $declaration): DeclarationFDOBrisPorte
    {
        if (null !== $this->dateOperation) {
            $declaration->setDateOperation($this->dateOperation);
        }

        return $declaration
            ->setEstErreur($this->estErreur)
            ->setDescriptionErreur($this->descriptionErreur)
            ->setAdresse($this->adresse?->versAdresse($declaration->getAdresse()))
            ->setProcedure($this->procedure?->versProcedureJudiciaire($declaration->getProcedure()))
            ->setCoordonneesRequerant($this->coordonneesRequerant?->versCoordonneesRequerant($declaration->getCoordonneesRequerant()))
            ->setPrecisionsRequerant($this->precisionsRequerant);
    }
}
