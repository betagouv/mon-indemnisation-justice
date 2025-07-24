<?php

namespace MonIndemnisationJustice\Api\Agent\Resources\Output;

use MonIndemnisationJustice\Api\Agent\Resources\Transformer\DossierTransformer;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: BrisPorte::class, transform: DossierTransformer::class)]
class DossierATransmettreOutput
{
    public int $id;
    public string $reference;
    public string $requerant;
    public float $montantIndemnisation;
    public \DateTimeInterface $dateValidation;
    public string $agentValidateur;
}
