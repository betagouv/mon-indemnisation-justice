<?php

namespace MonIndemnisationJustice\Api\Public\Dysfonctionnement\Input;

use MonIndemnisationJustice\Validation\Constraint\AvocatValide;
use Symfony\Component\Validator\Constraints as Assert;

#[AvocatValide]
class InscriptionAvocatInput extends InscriptionUsagerInput
{
    #[Assert\NotBlank(message: "Le barreau d'appartenance est requis")]
    public string $barreauId = '';

    #[Assert\NotBlank(message: 'Le numéro CNBF est requis')]
    #[Assert\Regex('/^\d{6}$/', message: 'Le numéro CNBF doit contenir 6 chiffres')]
    public string $numeroCnbf = '';
}
