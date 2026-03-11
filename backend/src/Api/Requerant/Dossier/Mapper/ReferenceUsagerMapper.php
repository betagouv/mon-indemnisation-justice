<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Mapper;

use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

class ReferenceUsagerMapper implements TransformCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): mixed
    {
        if ($value instanceof Usager) {
            return [
                'id' => $value->getId(),
                'nom' => $value->getNomCourant(capital: true),
            ];
        }

        return null;
    }
}
