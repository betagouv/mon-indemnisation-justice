<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\CoordonneesRequerant;
use Symfony\Component\Validator\Constraints as Assert;

class CoordonneesRequerantInput
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
        $input = new self();
        $input->civilite = $coordonnees->getCivilite();
        $input->nom = $coordonnees->getNom();
        $input->prenom = $coordonnees->getPrenom();
        $input->telephone = $coordonnees->getTelephone();
        $input->courriel = $coordonnees->getCourriel();

        return $input;
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
