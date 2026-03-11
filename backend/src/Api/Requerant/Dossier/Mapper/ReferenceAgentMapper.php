<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Mapper;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

class ReferenceAgentMapper implements TransformCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): mixed
    {
        if ($value instanceof Agent) {
            return [
                'id' => $value->getId(),
                'nom' => $value->getNomComplet(capital: true),
            ];
        }

        return null;
    }
}
