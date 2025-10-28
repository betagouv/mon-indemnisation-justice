<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Api\Agent\FDO\Transformers\TimestampToDateImmutableTransformer;
use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: DeclarationErreurOperationnelle::class)]
class DeclarationErreurOperationnelleInput
{
    #[Map(transform: TimestampToDateImmutableTransformer::class)]
    public int $dateOperation;
    #[Map(transform: TimestampToDateImmutableTransformer::class)]
    public int $dateCreation;
    public AdresseInput $adresse;
    public ProcedureInput $procedure;
    public InfosRequerantInput $infosRequerant;
    public string $commentaire;
}
