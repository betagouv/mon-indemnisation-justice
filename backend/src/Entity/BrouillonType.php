<?php

namespace MonIndemnisationJustice\Entity;

use MonIndemnisationJustice\Api\Requerant\Brouillon\Dto\DossierDto;

enum BrouillonType: string
{
    case BROUILLON_DOSSIER_BRIS_PORTE = 'BROUILLON_DOSSIER_BRIS_PORTE';

    public function getLibelle(): string
    {
        return match ($this) {
            self::BROUILLON_DOSSIER_BRIS_PORTE => 'Bris de porte',
        };
    }

    public function getEntiteCible(): string
    {
        return match ($this) {
            self::BROUILLON_DOSSIER_BRIS_PORTE => DossierDto::class,
        };
    }
}
