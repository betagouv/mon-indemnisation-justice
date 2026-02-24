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

    /**
     * Retourne la classe utilisée pour modéliser le brouillon en cours.
     *
     * @return string
     */
    public function getClasseTravail(): string
    {
        return match ($this) {
            self::BROUILLON_DOSSIER_BRIS_PORTE => DossierDto::class,
        };
    }

    /**
     * Retourne la classe utilisée pour engendrer une entité lors de la publication.
     *
     * @return string
     */
    public function getClassePublication(): string
    {
        return match ($this) {
            self::BROUILLON_DOSSIER_BRIS_PORTE => DossierDto::class,
        };
    }
}
