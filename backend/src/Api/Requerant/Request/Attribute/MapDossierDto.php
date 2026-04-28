<?php

namespace MonIndemnisationJustice\Api\Requerant\Request\Attribute;

use MonIndemnisationJustice\Entity\DossierType;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
/**
 * Permet de retrouver un DossierDto à partir de son identifiant `$reference`, id ou reference selon qu'il soit publié ou
 * encore en brouillon, et son $type de dossier. En cas de requête `POST` ou `PATCH`, le Dto est déjà modifie avec les
 * valeurs de la requête.
 */
class MapDossierDto
{
    public function __construct(
        public string $reference = 'reference',
        public DossierType $type = DossierType::BRIS_PORTE,
    ) {
    }
}
