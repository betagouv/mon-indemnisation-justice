<?php

namespace App\Dto;

use App\Entity\Civilite;
use App\Validation\Constraint\UniqueRequerantCourriel;
use Symfony\Component\Validator\Constraints as Assert;

class Inscription
{
    public Civilite $civilite;

    #[Assert\NotBlank(message: '', allowNull: false)]
    public string $prenom;

    #[Assert\NotBlank(message: '', allowNull: false)]
    public string $nom;

    public ?string $nomNaissance = null;

    #[Assert\NotBlank(message: '', allowNull: false)]
    #[Assert\Email(message: "L'adresse courriel n'est pas valide")]
    #[UniqueRequerantCourriel]
    public string $courriel;

    #[Assert\NotBlank(message: 'Le numéro de téléphone est manquant', allowNull: false)]
    public string $telephone;

    #[Assert\Length(min: 8, minMessage: 'Votre mot de passe doit contenir au moins 8 caractères')]
    #[Assert\Regex("/\d/", message: 'Votre mot de passe doit contenir au moins 1 chiffre')]
    public string $motDePasse = '';

    #[Assert\Expression(expression: 'this.motDePasse == this.confirmation', message: 'Les deux mots de passe doivent être identiques')]
    public string $confirmation = '';
}
