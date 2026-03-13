<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\PersonnePhysique;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class PersonnePhysiqueDto
{
    public PersonneDto $personne;

    public function __construct(
        ?PersonneDto $personne = null,
        public ?string $prenom2 = null,
        public ?string $prenom3 = null,
        public ?AdresseDto $adresse = null,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public ?\DateTimeImmutable $dateNaissance = null,
        public ?PaysDto $paysNaissance = null,
        public ?CommuneDto $communeNaissance = null,
        public ?string $villeNaissance = null,
    ) {
        $this->personne = $personne ?? new PersonneDto();
    }

    public function versPersonnePhysique(?PersonnePhysique $personnePhysique = null): PersonnePhysique
    {
        return ($personnePhysique ?? new PersonnePhysique())
            ->setPersonne($this->personne->versPersonne($personnePhysique?->getPersonne()))
            ->setPrenom2($this->prenom2)
            ->setPrenom3($this->prenom3)
            ->setAdresse($this->adresse?->versAdresse($personnePhysique?->getAdresse()))
            ->setDateNaissance($this->dateNaissance)
            ->setPaysNaissance($this->paysNaissance?->versPays($personnePhysique?->getPaysNaissance()))
            ->setCommuneNaissance($this->communeNaissance?->versCommune($personnePhysique?->getCodePostalNaissance()))
            ->setVilleNaissance(!is_null($this->villeNaissance) && !empty(trim($this->villeNaissance)) ? $this->villeNaissance : null);
    }

    public static function depuisPersonnePhysique(?PersonnePhysique $personnePhysique): ?self
    {
        if (null === $personnePhysique) {
            return null;
        }

        return new self(
            personne: PersonneDto::depuisPersonne($personnePhysique->getPersonne()),
            prenom2: $personnePhysique->getPrenom2(),
            prenom3: $personnePhysique->getPrenom2(),
            adresse: AdresseDto::depuisAdresse($personnePhysique->getAdresse()),
            dateNaissance: $personnePhysique->getDateNaissance() ? \DateTimeImmutable::createFromInterface($personnePhysique->getDateNaissance()) : null,
            paysNaissance: PaysDto::depuisPays($personnePhysique->getPaysNaissance()),
            communeNaissance: CommuneDto::depuisCommune($personnePhysique->getCodePostalNaissance()),
            villeNaissance: $personnePhysique->getVilleNaissance(),
        );
    }
}
