<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Request\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
/**
 * Permet de retrouver une déclaration FDO à partir de son identifiant `$id`.
 *
 * L'option `$modifie` permet de spécifier si on souhaite la déclaration modifiée par les données de la requête, si en
 * `PATCH`, ou la déclaration brute, telle qu'elle est en base de données.
 */
class MapDeclarationFDO
{
    public function __construct(
        public bool $modifie = true,
        public string $id = 'declarationId',
    ) {
    }
}
