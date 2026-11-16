<?php

namespace MonIndemnisationJustice\Service\Signature;

final readonly class EmplacementSignature
{
    public function __construct(
        public int $x,
        public int $y,
        public int $largeur,
        public int $hauteur,
        public int $numeroPage = 1,
    ) {

    }

}
