<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

class VerifierDossierInput
{
    public bool $estRecevable;

    public ?string $commentaire = null;

    /** @var array<PieceJointeValidationInput> */
    public array $piecesJointes = [];
}
