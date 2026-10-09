<?php

namespace MonIndemnisationJustice\Service;

use Random\RandomException;

/**
 * Service générant les codes d'invitation à déposer une demande.
 *
 * Le code est transmis dans un lien par mail : il doit être impossible à énumérer, d'où 128 bits d'entropie.
 */
class GenerateurCodeInvitation
{
    /**
     * @throws RandomException
     */
    public function generer(): string
    {
        return strtolower(bin2hex(random_bytes(16)));
    }
}
