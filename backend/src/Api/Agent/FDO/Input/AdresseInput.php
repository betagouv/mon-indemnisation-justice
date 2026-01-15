<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Input;

use MonIndemnisationJustice\Entity\Adresse;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: Adresse::class)]
class AdresseInput
{
    #[Assert\NotNull(message: "L'adresse est requise")]
    #[Assert\NotBlank(message: "L'adresse est requise")]
    public ?string $ligne1 = null;
    public ?string $ligne2 = null;
    #[Assert\NotNull(message: "L'adresse est requise")]
    #[Assert\Regex(pattern: '/\d{5-6}/', message: "Le code postal de l'adresse est requis")]
    public ?string $codePostal = null;
    #[Assert\NotNull(message: 'La ville est requise')]
    #[Assert\NotBlank(message: 'La ville est requise')]
    public ?string $localite = null;
}
