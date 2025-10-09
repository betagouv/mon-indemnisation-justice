<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Symfony\Component\Validator\Constraints as Assert;

final class GenererCourrierRejetInput
{
    #[Assert\Choice(choices: ['est_bailleur', 'est_vise', 'est_hebergeant', 'autre'], message: 'Le motif de rejet doit correspondre à un cas connu')]
    public ?string $motifRejet = null;
}
