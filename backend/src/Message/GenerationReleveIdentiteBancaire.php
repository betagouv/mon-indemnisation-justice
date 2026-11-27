<?php

namespace MonIndemnisationJustice\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\Uid\Uuid;

/**
 * Génère le relevé d'identité bancaire (PDF) d'une référence bancaire : l'impression prend quelques secondes.
 */
#[AsMessage('async')]
readonly class GenerationReleveIdentiteBancaire
{
    public function __construct(
        public Uuid $idReferenceBancaire,
        public ?int $idDossier = null,
    ) {
    }
}
