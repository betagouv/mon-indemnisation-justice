<?php

namespace MonIndemnisationJustice\Validation\Constraint;

use MonIndemnisationJustice\Validation\Validator\AvocatValideValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Vérifie, pour une inscription avocat, que le numéro CNBF correspond bien à un avocat connu de
 * l'annuaire importé, qu'il n'est pas déjà rattaché à un compte existant, et que le barreau donné
 * correspond bien à celui de cet avocat.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AvocatValide extends Constraint
{
    public string $messageInconnu = "Ce numéro CNBF n'est pas reconnu dans l'annuaire des avocats.";
    public string $messageDejaInscrit = 'Ce numéro CNBF est déjà rattaché à un compte existant, nous vous invitons à vous connecter.';
    public string $messageBarreauIncorrect = "Ce numéro CNBF n'est pas rattaché au barreau sélectionné.";

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return AvocatValideValidator::class;
    }
}
