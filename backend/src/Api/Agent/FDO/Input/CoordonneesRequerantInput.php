<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\CoordonneesRequerant;
use Symfony\Component\Validator\Constraints as Assert;

class CoordonneesRequerantInput
{
    public ?Civilite $civilite = null;
    #[Assert\NotNull(message: 'Le nom du requérant est requis')]
    public ?string $nom = null;
    #[Assert\NotNull(message: 'Le prénom du requérant est requis')]
    public ?string $prenom = null;
    public ?string $telephone = null;
    #[Assert\NotNull(message: "L'adresse courriel du requérant est requise")]
    #[Assert\Email(message: "L'adresse courriel du requérant est invalide")]
    public ?string $courriel = null;

    public function versCoordonneesRequerant(): CoordonneesRequerant
    {
        return new CoordonneesRequerant()
            ->setCivilite($this->civilite)
            ->setNom($this->nom)
            ->setPrenom($this->prenom)
            ->setTelephone($this->telephone)
            ->setCourriel($this->courriel);
    }
}
