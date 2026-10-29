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
    #[Assert\Valid]
    public ?AdresseInput $adresse = null;
    #[Assert\NotNull(message: 'La procédure judiciaire est requise')]
    #[Assert\Valid]
    public ?ProcedureJudiciaireInput $procedure = null;
    public ?string $precisionsRequerant = null;
    #[Assert\Valid]
    public ?CoordonneesRequerantInput $coordonneesRequerant = null;

    public static function depuisDeclaration(DeclarationFDOBrisPorte $declaration): self
    {
        $input = new self();
        $input->id = $declaration->getId();
        $input->dateOperation = $declaration->getDateOperation();
        $input->estErreur = $declaration->getEstErreur();
        $input->descriptionErreur = $declaration->getDescriptionErreur();
        $input->adresse = $declaration->getAdresse() ? AdresseInput::depuisAdresse($declaration->getAdresse()) : null;
        $input->procedure = $declaration->getProcedure() ? ProcedureJudiciaireInput::depuisProcedureJudiciaire($declaration->getProcedure()) : null;
        $input->precisionsRequerant = $declaration->getPrecisionsRequerant();
        $input->coordonneesRequerant = $declaration->getCoordonneesRequerant() ? CoordonneesRequerantInput::depuisCoordonneesRequerant($declaration->getCoordonneesRequerant()) : null;

        return $input;
    }

    /**
     * Reporte les valeurs de l'input sur la déclaration (et ses entités liées) fournie.
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
