<?php

namespace MonIndemnisationJustice\Event\Event;

use MonIndemnisationJustice\Entity\Dossier;

abstract class DossierTransitionEvent
{
    public function __construct(
        public readonly Dossier $dossier,
    ) {
    }
}
