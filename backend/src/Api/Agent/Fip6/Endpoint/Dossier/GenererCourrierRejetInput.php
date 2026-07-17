<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Entity\MotifRejetBrisPorte;
use Symfony\Component\Validator\Constraints as Assert;

final class GenererCourrierRejetInput
{
    #[Assert\Choice(callback: [MotifRejetBrisPorte::class, 'cases'], message: 'Le motif de rejet doit correspondre à un cas connu')]
    public ?MotifRejetBrisPorte $motifRejet = null;
}
