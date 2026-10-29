<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Dto;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\CoordonneesRequerant;
use Symfony\Component\Validator\Constraints as Assert;

class CoordonneesRequerantDto
{
    public ?Civilite $civilite = null;
    #[Assert\NotBlank(message: 'Le nom du requérant est requis')]
    public ?string $nom = null;
    #[Assert\NotBlank(message: 'Le prénom du requérant est requis')]
    public ?string $prenom = null;
    public ?string $telephone = null;
    #[Assert\NotBlank(message: "L'adresse courriel du requérant est requise")]
    #[Assert\Email(message: "L'adresse courriel du requérant est invalide")]
    public ?string $courriel = null;

    public static function depuisCoordonneesRequerant(CoordonneesRequerant $coordonnees): self
    {
        $dto = new self();
        $dto->civilite = $coordonnees->getCivilite();
        $dto->nom = $coordonnees->getNom();
        $dto->prenom = $coordonnees->getPrenom();
        $dto->telephone = $coordonnees->getTelephone();
        $dto->courriel = $coordonnees->getCourriel();

        return $dto;
    }

    public function versCoordonneesRequerant(?CoordonneesRequerant $coordonnees = null): CoordonneesRequerant
    {
        return ($coordonnees ?? new CoordonneesRequerant())
            ->setCivilite($this->civilite)
            ->setNom($this->nom ?? '')
            ->setPrenom($this->prenom ?? '')
            ->setTelephone($this->telephone ?? '')
            ->setCourriel($this->courriel ?? '');
    }
}
