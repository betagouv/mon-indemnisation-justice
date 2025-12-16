<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: DeclarationFDOBrisPorte::class)]
class DeclarationFDOBrisPorteInput
{
    public \DateTimeImmutable $dateOperation;
    public \DateTimeImmutable $dateCreation;
    public DeclarationFDOBrisPorteErreurType $estErreur;
    public ?string $descriptionErreur = null;
    public AdresseInput $adresse;
    public ProcedureInput $procedure;
    public string $telephone;
    public ?string $precisionsRequerant = null;
    public ?InfosRequerantInput $infosRequerant = null;
}
