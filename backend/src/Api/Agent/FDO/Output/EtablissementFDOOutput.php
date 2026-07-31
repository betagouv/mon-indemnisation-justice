<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\FDO\EtablissementFDO;

class EtablissementFDOOutput
{
    public function __construct(
        public readonly string $id,
        public readonly string $nom,
        public readonly string $identifiant,
    ) {

    }

    public static function depuisEtablissementFDO(EtablissementFDO $etablissement): EtablissementFDOOutput
    {
        return new EtablissementFDOOutput(
            id: $etablissement->getId(),
            nom: $etablissement->getNom(),
            identifiant: $etablissement->getIdentifiant(),
        );
    }
}
