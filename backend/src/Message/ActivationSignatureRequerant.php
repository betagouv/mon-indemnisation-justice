<?php

namespace MonIndemnisationJustice\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\Uid\Uuid;

/**
 * Active la requête de signature : la déclaration d'acceptation est générée d'après la référence bancaire rattachée à
 * la requête.
 */
#[AsMessage('async')]
readonly class ActivationSignatureRequerant
{
    public function __construct(
        public Uuid $id,
    ) {
    }
}
