<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Mapper;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\EtatDossierDto;
use MonIndemnisationJustice\Entity\EtatDossier;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

/**
 * On doit utiliser ce mapper, car on ne peut pas transformer et spécifier une cible différente avec l'objet mapper.
 *
 * Or, lorsque l'on mappe un `Dossier` vers un `DossierDto`,on doit convertir l'`EtatDossier` en `EtatDossierDto` depuis
 * `etatDossier` vers `etatActuel`.
 */
class EtatDossierDtoMapper implements TransformCallableInterface
{
    public function __construct(
        protected readonly ObjectMapperInterface $mapper,
    ) {
    }

    public function __invoke(mixed $value, object $source, ?object $target): mixed
    {

        if ($target instanceof DossierDto && $value instanceof EtatDossier) {
            return $this->mapper->map($value, EtatDossierDto::class);
        }

        return null;
    }
}
