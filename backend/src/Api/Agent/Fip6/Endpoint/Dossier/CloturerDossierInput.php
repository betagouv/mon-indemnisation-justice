<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Symfony\Component\Validator\Constraints as Assert;

final class CloturerDossierInput
{
    #[Assert\NotNull(message: 'Le motif de clôture est manquant')]
    public string $motif;

    #[Assert\NotNull(message: "L'explication de la clôture est manquant")]
    public string $explication;
}
