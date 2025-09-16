<?php

namespace MonIndemnisationJustice\Api\Agent\Fip3\Transformers;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

class AgentIdTransformer implements TransformCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): ?int
    {
        if ($value instanceof Agent) {
            return $value->getId();
        }

        return null;
    }
}
