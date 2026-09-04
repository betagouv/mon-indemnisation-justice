<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Symfony\Component\Validator\Constraints as Assert;

final class AnnoterDossierInput
{
    #[Assert\NotNull(message: 'Le corps de la note est requis')]
    public string $notes;
}
