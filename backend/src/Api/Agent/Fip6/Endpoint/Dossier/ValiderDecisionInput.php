<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

final class ValiderDecisionInput
{
    public bool $estValide = true;

    public ?float $montantIndemnisation = null;
}
