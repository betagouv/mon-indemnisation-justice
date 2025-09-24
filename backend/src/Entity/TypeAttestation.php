<?php

namespace MonIndemnisationJustice\Entity;

enum TypeAttestation: string
{
    case NOUVELLE_ATTESTATION = 'NOUVELLE_ATTESTATION';
    case ANCIENNE_ATTESTATION = 'ANCIENNE_ATTESTATION';
    case COURRIER_FDO = 'COURRIER_FDO';
    case PAS_ATTESTATION = 'PAS_ATTESTATION';

    public function priorite(): int
    {
        return match ($this) {
            self::NOUVELLE_ATTESTATION => 1,
            self::ANCIENNE_ATTESTATION => 2,
            self::COURRIER_FDO => 3,
            self::PAS_ATTESTATION => 4
        };
    }

    public function getPrioritaire(?TypeAttestation $autre): TypeAttestation
    {
        if (null === $autre) {
            return $this;
        }

        return $this->priorite() >= $autre->priorite() ? $this : $autre;
    }
}
