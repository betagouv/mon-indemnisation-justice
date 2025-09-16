<?php

namespace MonIndemnisationJustice\Api\Agent\Fip3\Endpoint\Dossier;

use Symfony\Component\Validator\Constraints as Assert;

final class AttribuerDossierInput
{
    #[Assert\NotNull(message: "L'id du rédacteur est manquant")]
    public ?int $redacteur_id = null;
}
