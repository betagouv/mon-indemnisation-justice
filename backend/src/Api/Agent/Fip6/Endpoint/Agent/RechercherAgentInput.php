<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Entity\AdministrationType;

readonly class RechercherAgentInput
{
    public function __construct(
        // Numéro de la page
        public int $page = 1,
        // Nombre de résultats par page
        public int $taille = 20,
        // Agents actifs ou non
        public bool $actif = true,
        /**
         * @var array<AdministrationType>
         */
        public ?array $administrations = null,
        public ?string $recherche = null,
    ) {
    }

}
