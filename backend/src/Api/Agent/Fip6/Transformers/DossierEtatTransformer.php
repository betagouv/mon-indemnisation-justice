<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Transformers;

use MonIndemnisationJustice\Entity\EtatDossierType;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

class DossierEtatTransformer implements TransformCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): ?string
    {
        return $value instanceof EtatDossierType ? $value->value : null;
    }
}
