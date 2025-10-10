<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Symfony\Component\Validator\Constraints as Assert;

final class GenererCourrierPropositionIndemnisationInput
{
    #[Assert\NotNull(message: "Le montant de l'indemnisation doit être fourni")]
    public float $montantIndemnisation;
}
