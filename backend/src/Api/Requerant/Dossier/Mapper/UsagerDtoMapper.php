<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Mapper;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\PersonneDto;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\UsagerDto;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

class UsagerDtoMapper implements TransformCallableInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ObjectMapperInterface $mapper,
    ) {
    }

    public function __invoke(mixed $value, object $source, ?object $target): mixed
    {
        if ($target instanceof DossierDto && $value instanceof Usager) {
            return $value->getId();
        }

        if ($source instanceof Usager && $value instanceof UsagerDto) {
            // Grosse rustine : object mapper ne veut pas mapper la personne, donc on se replie ici
            $target = new UsagerDto();
            $target->personne = $this->mapper->map($source->getPersonne(), PersonneDto::class);

            return $target;
        }

        if ($target instanceof Dossier && is_int($value)) {
            return $this->em->getRepository(Usager::class)->find($value);
        }

        return null;
    }
}
