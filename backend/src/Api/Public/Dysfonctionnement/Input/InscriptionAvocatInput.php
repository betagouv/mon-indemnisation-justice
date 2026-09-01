<?php

namespace MonIndemnisationJustice\Api\Public\Dysfonctionnement\Input;

use MonIndemnisationJustice\Validation\Constraint\AvocatConnu;
use MonIndemnisationJustice\Validation\Constraint\AvocatValide;
use MonIndemnisationJustice\Validation\Constraint\UniqueAvocatCnbf;
use Symfony\Component\Validator\Constraints as Assert;

// AvocatValide (contrainte de classe) ne porte plus que sur la cohérence numeroCnbf <-> barreauId ;
// les règles sur le seul numéro CNBF sont listées ci-dessous sur la propriété.
#[AvocatValide]
class InscriptionAvocatInput extends InscriptionUsagerInput
{
    #[Assert\NotBlank(message: "Le barreau d'appartenance est requis")]
    public string $barreauId = '';

    #[Assert\NotBlank(message: 'Le numéro CNBF est requis')]
    #[Assert\Regex('/^\d{6}$/', message: 'Le numéro CNBF doit contenir 6 chiffres')]
    #[AvocatConnu]
    #[UniqueAvocatCnbf]
    public string $numeroCnbf = '';
}
