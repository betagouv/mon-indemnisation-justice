<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Document;

use MonIndemnisationJustice\Entity\Document;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(Document::class)]
final class ImprimerDocumentInput
{
    #[Assert\NotBlank(message: 'Le corps du courrier est requis')]
    public ?string $corps = null;
}
