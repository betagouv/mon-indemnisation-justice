<?php

namespace MonIndemnisationJustice\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ModificationMotDePasse
{
    #[Assert\Length(min: 8, minMessage: "Votre mot de passe doit contenir au moins 8 caractères")]
    #[Assert\Regex("/\d/", message: "Votre mot de passe doit contenir au moins 1 chiffre")]
    public string $motDePasse = '';

    #[Assert\Expression(expression: 'this.motDePasse == this.confirmation', message: 'Les deux mots de passe doivent être identiques')]
    public string $confirmation = '';
}
