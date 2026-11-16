<?php

namespace MonIndemnisationJustice\Service\Signature\Evenement;

use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;

final readonly class EvenementSignature
{
    public function __construct(
        public RequeteSignatureRequerant $requeteSignatureRequerant,
        public EvenementSignatureType $type,
        public \DateTimeImmutable $date = new \DateTimeImmutable(),
        public ?string $erreur = null,
    ) {
    }
}
