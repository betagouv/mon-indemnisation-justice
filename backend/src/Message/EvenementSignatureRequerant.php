<?php

namespace MonIndemnisationJustice\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class EvenementSignatureRequerant
{
    public function __construct(public readonly array $donnees)
    {
    }
}
