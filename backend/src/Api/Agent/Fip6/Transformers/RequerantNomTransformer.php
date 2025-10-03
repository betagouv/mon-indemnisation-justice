<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Transformers;

use MonIndemnisationJustice\Entity\Requerant;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

class RequerantNomTransformer implements TransformCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): ?string
    {
        if (!$value instanceof Requerant) {
            return null;
        }

        return $value->getNomCourant(capital: true);
    }
}
