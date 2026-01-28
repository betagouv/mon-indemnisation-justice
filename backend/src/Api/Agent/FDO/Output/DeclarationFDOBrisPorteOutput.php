<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Api\Agent\FDO\Input\DocumentDto;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;

#[Map(source: DeclarationFDOBrisPorte::class)]
class DeclarationFDOBrisPorteOutput
{
    public Uuid $id;
    public ?string $reference;
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public ?\DateTimeInterface $dateOperation;

    public ?DeclarationFDOBrisPorteErreurType $estErreur;
    public ?string $descriptionErreur = null;

    #[Map(target: AdresseOutput::class)]
    public ?AdresseOutput $adresse = null;

    #[Map(target: CoordonneesRequerantOutput::class)]
    public ?CoordonneesRequerantOutput $coordonneesRequerant;
    public ?string $precisionsRequerant;

    #[Map(target: ProcedureJudiciaireOutput::class)]
    public ProcedureJudiciaireOutput $procedure;

    #[Map(target: AgentOutput::class)]
    public ?AgentOutput $agent = null;

    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::W3C])]
    public ?\DateTimeInterface $dateCreation;
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::W3C])]
    public ?\DateTimeInterface $dateSoumission;

    /** @param DocumentDto[] $piecesJointes */
    #[Map(source: 'piecesJointes.toArray')]
    public array $piecesJointes = [];
}
