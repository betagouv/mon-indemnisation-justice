<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Request\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
/**
 * Permet de retrouver le DeclarationFDOBrisPorteDto d'une déclaration FDO de bris de porte à partir de son identifiant
 * `$id`. En cas de requête `PATCH`, le Dto est déjà modifié avec les valeurs de la requête.
 */
class MapDeclarationFDODto
{
    public function __construct(
        public string $id = 'declarationId',
    ) {
    }
}
