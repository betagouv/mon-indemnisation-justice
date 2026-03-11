<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Mapper;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\AdresseDto;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\Dossier;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

/**
 * On doit utiliser ce mapper, car on ne peut pas transformer et spécifier une cible différente avec l'objet mapper.
 *
 * Or, lorsque l'on mappe un `DossierDto` vers `Dossier`,on doit convertir l'`AdresseDto` en `Adresse` et l'assigner à
 * `brisPorte.adresse`.
 */
class AdresseDtoMapper implements TransformCallableInterface
{
    public function __construct(
        protected readonly ObjectMapperInterface $mapper,
    ) {
    }

    public function __invoke(mixed $value, object $source, ?object $target): mixed
    {
        if ($target instanceof Dossier && $value instanceof AdresseDto) {
            return $this->mapper->map($value, Adresse::class);
        }

        return null;
    }
}
