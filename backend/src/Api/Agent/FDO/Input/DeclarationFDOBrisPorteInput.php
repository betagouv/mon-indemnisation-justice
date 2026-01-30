<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: DeclarationFDOBrisPorte::class)]
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

    #[Map(if: false)]
    /*
     * Puisque l'object mapper ne sait pas encore gérer le mapping récursif, les pièces jointes ne seront pas propagées
     * vers la déclaration.
     *
     * /!\ il faut donc _manuellement_ mapper et ajouter les pièces jointes à la déclaration cible.
     */
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
}
