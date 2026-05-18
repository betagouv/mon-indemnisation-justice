<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Document;

use MonIndemnisationJustice\Entity\AdministrationType;
use MonIndemnisationJustice\Entity\Metadonnees\MetadonneesAttestation;
use MonIndemnisationJustice\Entity\TypeAttestation;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(MetadonneesAttestation::class)]
class RenseignerMetaDonneesAttestationInput
{
    #[Assert\NotNull(message: "Le type d'attestation doit être fourni")]
    #[Assert\Choice(callback: [TypeAttestation::class, 'cases'])]
    public ?TypeAttestation $typeAttestation;
    #[Assert\Choice(callback: [AdministrationType::class, 'cases'])]
    public ?AdministrationType $typeAdministration = null;

    public ?\DateTimeImmutable $dateOperation = null;
}
