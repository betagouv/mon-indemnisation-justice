<?php

namespace MonIndemnisationJustice\Message;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\ContexteSignatureElectroniqueDto;
use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\Uid\Uuid;

#[AsMessage('async')]
readonly class ActivationSignatureRequerant
{
    public function __construct(
        public Uuid $id,
        public ContexteSignatureElectroniqueDto $contexte,
    ) {
    }
}
