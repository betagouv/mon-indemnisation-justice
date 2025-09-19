<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Transformers\AgentIdTransformer;
use MonIndemnisationJustice\Api\Agent\Fip6\Transformers\DossierEtatTransformer;
use MonIndemnisationJustice\Entity\EtatDossier;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[Map(source: EtatDossier::class)]
final class AttribuerDossierOutput
{
    public int $id;
    #[Map(transform: [DossierEtatTransformer::class])]
    public string $etat;
    #[Map(source: 'date')]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    public \DateTimeInterface $dateEntree;
    #[Map(source: 'agent', transform: [AgentIdTransformer::class])]
    public int $agent;
    // public string $redacteur;
    // public ?array $contexte;
}
