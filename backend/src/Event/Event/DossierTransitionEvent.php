<?php

namespace MonIndemnisationJustice\Event\Event;

use MonIndemnisationJustice\Entity\BrisPorte;

abstract class DossierTransitionEvent
{
    public function __construct(
        public readonly BrisPorte $dossier,
    ) {}
}
