<?php

namespace MonIndemnisationJustice\Event;

use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Contracts\EventDispatcher\Event;

class BrisPorteConstitueEvent extends Event
{
    public function __construct(public readonly BrisPorte $brisPorte)
    {
    }
}
