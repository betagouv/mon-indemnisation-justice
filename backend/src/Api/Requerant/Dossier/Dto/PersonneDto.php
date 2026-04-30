<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\Personne;

class PersonneDto
{
    public function __construct(
        public ?string $id = null,
        public ?Civilite $civilite = null,
        public ?string $nom = null,
        public ?string $nomNaissance = null,
        public ?string $prenom = null,
        public ?string $courriel = null,
        public ?string $telephone = null,
    ) {
    }

    public function versPersonne(?Personne $personne = null): Personne
    {
        return ($personne ?? new Personne())
            ->setCivilite($this->civilite)
            ->setNom($this->nom)
            ->setNomNaissance($this->nomNaissance)
            ->setPrenom($this->prenom)
            ->setCourriel($this->courriel)
            ->setTelephone($this->telephone);
    }

    public static function depuisPersonne(?Personne $personne): ?self
    {
        if (null === $personne) {
            return null;
        }

        return new self(
            id: $personne->getId(),
            civilite: $personne->getCivilite(),
            nom: $personne->getNom(),
            nomNaissance: $personne->getNomNaissance(),
            prenom: $personne->getPrenom(),
            courriel: $personne->getCourriel(),
            telephone: $personne->getTelephone(),
        );
    }
}
