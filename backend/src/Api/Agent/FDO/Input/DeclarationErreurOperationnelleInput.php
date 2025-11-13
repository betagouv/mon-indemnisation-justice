<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: DeclarationErreurOperationnelle::class)]
class DeclarationErreurOperationnelleInput
{
    public \DateTimeImmutable $dateOperation;
    public \DateTimeImmutable $dateCreation;
    public AdresseInput $adresse;
    public ProcedureInput $procedure;
    public string $telephone;
    public InfosRequerantInput $infosRequerant;
    public string $commentaire;
}
