<?php

namespace MonIndemnisationJustice\Service\Signature;

use MonIndemnisationJustice\Entity\Dossier;

interface GestionnaireSignatureRequerantInterface
{
    public function creerRequeteSignature(Dossier $dossier): void;
}
