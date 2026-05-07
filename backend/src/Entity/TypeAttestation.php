<?php

namespace MonIndemnisationJustice\Entity;

enum TypeAttestation: string
{
    case NOUVELLE_ATTESTATION = 'NOUVELLE_ATTESTATION';
    case AVIS_INTERVENTION = 'AVIS_INTERVENTION';
    case ANCIENNE_ATTESTATION = 'ANCIENNE_ATTESTATION';
    case COURRIER_FDO = 'COURRIER_FDO';
    case PAS_ATTESTATION = 'PAS_ATTESTATION';

    public function priorite(): int
    {
        return match ($this) {
            self::NOUVELLE_ATTESTATION => 1,
            self::AVIS_INTERVENTION => 2,
            self::ANCIENNE_ATTESTATION => 3,
            self::COURRIER_FDO => 4,
            self::PAS_ATTESTATION => 5,
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
