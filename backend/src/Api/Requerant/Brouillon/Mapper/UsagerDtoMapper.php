<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Mapper;

use MonIndemnisationJustice\Api\Requerant\Brouillon\Dto\PersonneDto;
use MonIndemnisationJustice\Api\Requerant\Brouillon\Dto\UsagerDto;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

class UsagerDtoMapper implements TransformCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): mixed
    {
        if ($source instanceof Usager) {

            return $this->fromUsager($source);
        }

        return null;
    }

    protected function fromUsager(Usager $usager): UsagerDto
    {
        $dto = new UsagerDto();
        $dto->id = $usager->getId();

        if ($usager->getPersonne()) {
            $personneDto = new PersonneDto();
            $personneDto->id = $usager->getPersonne()->getId()?->toString();
            $personneDto->civilite = $usager->getPersonne()->getCivilite();
            $personneDto->nom = $usager->getPersonne()->getNom();
            $personneDto->nomNaissance = $usager->getPersonne()->getNomNaissance();
            $personneDto->prenom = $usager->getPersonne()->getPrenom();
            $personneDto->courriel = $usager->getPersonne()->getCourriel();
            $personneDto->telephone = $usager->getPersonne()->getTelephone();

            $dto->personne = $personneDto;
        }

        return $dto;
    }
}
