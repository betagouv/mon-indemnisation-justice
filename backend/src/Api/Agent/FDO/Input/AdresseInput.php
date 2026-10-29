<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Adresse;
use Symfony\Component\Validator\Constraints as Assert;

class AdresseInput
{
    #[Assert\NotBlank(message: "L'adresse est requise")]
    public ?string $ligne1 = null;
    public ?string $ligne2 = null;
    #[Assert\NotBlank(message: "Le code postal de l'adresse est requis")]
    #[Assert\Regex(pattern: '/^\d{5}$/', message: "Le code postal de l'adresse est invalide")]
    public ?string $codePostal = null;
    #[Assert\NotBlank(message: 'La ville est requise')]
    public ?string $localite = null;

    public static function depuisAdresse(Adresse $adresse): self
    {
        $input = new self();
        $input->ligne1 = $adresse->getLigne1();
        $input->ligne2 = $adresse->getLigne2();
        $input->codePostal = $adresse->getCodePostal();
        $input->localite = $adresse->getLocalite();

        return $input;
    }

    public function versAdresse(?Adresse $adresse = null): Adresse
    {
        return ($adresse ?? new Adresse())
            ->setLigne1($this->ligne1)
            ->setLigne2($this->ligne2)
            ->setCodePostal($this->codePostal)
            ->setLocalite($this->localite);
    }
}
