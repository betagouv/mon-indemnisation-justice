<?php

namespace MonIndemnisationJustice\Event;

use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Contracts\EventDispatcher\Event;

class DossierConstitueEvent extends Event
{
    public function __construct(public readonly BrisPorte $brisPorte)
    {
    }
}
