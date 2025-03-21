<?php

namespace MonIndemnisationJustice\Validation\Constraint;

use MonIndemnisationJustice\Validation\Validator\UniqueRequerantCourrielValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueRequerantCourriel extends Constraint
{
    public string $message = 'Cette adresse est déjà utilisée, nous vous invitons à vous connecter';

    public function validatedBy(): string
    {
        return UniqueRequerantCourrielValidator::class;
    }
}
