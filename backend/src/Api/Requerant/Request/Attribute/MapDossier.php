<?php

namespace MonIndemnisationJustice\Api\Requerant\Request\Attribute;

use MonIndemnisationJustice\Entity\DossierType;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
/**
 * Permet de retrouver un dossier à partir de son identifiant `$reference`, id ou reference selon qu'il soit publié ou encore en
 * brouillon, et son $type de dossier.
 *
 * L'option `$modifie` permet de spécifier si on souhaite le dossier modifié par les données de la requête, si en `POST`
 * ou `PATCH`, ou le dossier brut, tel qu'il est en base de données.
 */
class MapDossier
{
    public function __construct(
        public bool $modifie = true,
        public string $reference = 'reference',
        public DossierType $type = DossierType::BRIS_PORTE,
    ) {
    }
}
