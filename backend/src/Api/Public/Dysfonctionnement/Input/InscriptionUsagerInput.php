<?php

namespace MonIndemnisationJustice\Api\Public\Dysfonctionnement\Input;

use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Validation\Constraint\UniqueRequerantCourriel;
use Symfony\Component\Validator\Constraints as Assert;

class InscriptionUsagerInput
{
    #[Assert\NotNull(message: 'La civilité est requise')]
    public ?Civilite $civilite = null;

    #[Assert\NotBlank(message: 'Le prénom est requis')]
    public string $prenom = '';

    #[Assert\NotBlank(message: 'Le nom est requis')]
    public string $nom = '';

    public ?string $nomNaissance = null;

    #[Assert\NotBlank(message: "L'adresse email est requise")]
    #[Assert\Email(message: "L'adresse email n'est pas valide")]
    #[UniqueRequerantCourriel]
    public string $courriel = '';

    #[Assert\NotBlank(message: 'Le numéro de téléphone est requis')]
    public string $telephone = '';

    #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins 8 caractères')]
    #[Assert\Regex('/\d/', message: 'Le mot de passe doit contenir au moins 1 chiffre')]
    public string $motDePasse = '';

    #[Assert\EqualTo(propertyPath: 'motDePasse', message: 'Les deux mots de passe doivent être identiques')]
    public string $confirmation = '';

    #[Assert\IsTrue(message: "Vous devez accepter les conditions générales d'utilisation")]
    public bool $cguOk = false;

    public bool $estPersonneMorale = false;
}
