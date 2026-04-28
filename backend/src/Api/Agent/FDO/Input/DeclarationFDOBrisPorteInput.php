<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class DeclarationFDOBrisPorteInput
{
    public Uuid $id;
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Assert\NotNull(message: "La date de l'opération ayant conduit au bris de porte est requise")]
    public ?\DateTimeImmutable $dateOperation = null;
    #[Assert\NotNull(message: "Veuillez-nous indiquer s'il s'agit d'une erreur")]
    public ?DeclarationFDOBrisPorteErreurType $estErreur = null;
    public ?string $descriptionErreur = null;
    #[Assert\NotNull(message: "L'adresse du bris de porte est requise")]
    public ?AdresseInput $adresse = null;
    #[Assert\NotNull(message: 'La procédure judiciaire est requise')]
    #[Assert\Valid]
    public ?ProcedureJudiciaireInput $procedure = null;
    public ?string $precisionsRequerant = null;
    #[Assert\Valid]
    public ?CoordonneesRequerantInput $coordonneesRequerant = null;

    protected array $piecesJointes = [];

    public function getPiecesJointes(): array
    {
        return $this->piecesJointes;
    }

    /** @param DocumentDto[] $piecesJointes */
    public function setPiecesJointes(array $piecesJointes): DeclarationFDOBrisPorteInput
    {
        $this->piecesJointes = $piecesJointes;

        return $this;
    }

    public function versDeclaration(DeclarationFDOBrisPorte $declarationFDOBrisPorte): DeclarationFDOBrisPorte
    {
        return $declarationFDOBrisPorte
            ->setId($this->id)
            ->setDateOperation($this->dateOperation)
            ->setEstErreur($this->estErreur)
            ->setDescriptionErreur($this->descriptionErreur)
            ->setAdresse($this->adresse->versAdresse())
            ->setProcedure($this->procedure->versProcedureJudiciaire())
            ->setCoordonneesRequerant($this->coordonneesRequerant?->versCoordonneesRequerant())
            ->setPrecisionsRequerant($this->precisionsRequerant)
            ->setPiecesJointes(
                array_map(
                    fn (DocumentDto $documentDto) => $documentDto->versDocument(),
                    $this->piecesJointes
                )
            );

    }
}
