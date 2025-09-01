<?php

namespace MonIndemnisationJustice\Event;

use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Contracts\EventDispatcher\Event;

class DossierDecideEvent extends Event
{
    public function __construct(public readonly BrisPorte $dossier)
    {
    }

    public function estAccepte(): bool
    {
        return $this->dossier->estAccepte();
    }
}
