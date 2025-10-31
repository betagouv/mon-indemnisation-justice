<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;

#[Map(source: DeclarationErreurOperationnelle::class)]
class DeclarationErreurOperationnelleOutput
{
    public ?Uuid $id = null;
    public string $reference;
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::W3C])]
    public \DateTimeInterface $dateOperation;
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::W3C])]
    public \DateTimeInterface $dateCreation;
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::W3C])]
    public \DateTimeInterface $dateSoumission;
    public string $commentaire;

    #[Map(target: AdresseOutput::class)]
    public AdresseOutput $adresse;

    #[Map(target: InfosRequerantOutput::class)]
    public InfosRequerantOutput $infosRequerant;

    #[Map(target: ProcedureOutput::class)]
    public ProcedureOutput $procedure;
}
