<?php

namespace MonIndemnisationJustice\Entity\Metadonnees;

use Symfony\Component\Uid\Uuid;

/**
 * Classe de métadonnées utilisée pour modéliser le champ `Requerant.navigation`.
 *
 * On y trouve
 */
readonly class NavigationRequerant extends AbstractMetadonnees
{
    public function __construct(
        public ?int $idTestEligibilite = null,
        public null|string|Uuid $idDeclaration = null,
    ) {}
}
