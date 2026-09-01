<?php

namespace MonIndemnisationJustice\Validation\Constraint;

use MonIndemnisationJustice\Validation\Validator\UniqueAvocatCnbfValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Vérifie qu'aucun compte usager n'est déjà rattaché au numéro CNBF porté par la propriété.
 *
 * Sur le modèle de {@see UniqueRequerantCourriel}. Ne protège pas contre deux inscriptions concurrentes
 * pour le même avocat : seule la contrainte unique en base (usagers.avocat_id) le fait, cf.
 * InscrireAvocatEndpoint.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UniqueAvocatCnbf extends Constraint
{
    public string $message = 'Ce numéro CNBF est déjà rattaché à un compte existant, nous vous invitons à vous connecter.';

    public function validatedBy(): string
    {
        return UniqueAvocatCnbfValidator::class;
    }
}
