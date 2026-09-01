<?php

namespace MonIndemnisationJustice\Validation\Constraint;

use MonIndemnisationJustice\Validation\Validator\AvocatConnuValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Vérifie que le numéro CNBF porté par la propriété correspond bien à un avocat présent dans l'annuaire
 * importé (cf. ImporteurAvocat). N'agit que sur un numéro déjà bien formé : les valeurs vides ou mal
 * formées relèvent des contraintes NotBlank/Regex.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AvocatConnu extends Constraint
{
    public string $message = "Ce numéro CNBF n'est pas reconnu dans l'annuaire des avocats.";

    public function validatedBy(): string
    {
        return AvocatConnuValidator::class;
    }
}
