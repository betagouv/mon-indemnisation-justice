<?php

namespace MonIndemnisationJustice\Validation\Constraint;

use MonIndemnisationJustice\Validation\Validator\AvocatValideValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Contrainte de classe pour l'inscription avocat : vérifie la cohérence entre le numéro CNBF et le barreau
 * sélectionné (le barreau donné doit être celui auquel l'avocat est rattaché dans l'annuaire).
 *
 * Les deux autres règles portant sur le seul numéro CNBF sont des contraintes de propriété posées
 * directement sur InscriptionAvocatInput::$numeroCnbf : {@see AvocatConnu} (présence dans l'annuaire) et
 * {@see UniqueAvocatCnbf} (pas déjà rattaché à un compte).
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AvocatValide extends Constraint
{
    public string $message = "Ce numéro CNBF n'est pas rattaché au barreau sélectionné.";

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return AvocatValideValidator::class;
    }
}
