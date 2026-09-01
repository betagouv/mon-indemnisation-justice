<?php

namespace MonIndemnisationJustice\Entity;

enum DossierType: string
{
    case BRIS_PORTE = 'BRIS_PORTE';
    case DYSFONCTIONNEMENT = 'DYSFONCTIONNEMENT';

    /**
     * Retourne le code à 3 caractères utilisé dans le préfixe de référence d'un dossier.
     *
     * @return void
     */
    public function getCodeReference(): string
    {
        return match ($this) {
            self::BRIS_PORTE => 'BRI',
            self::DYSFONCTIONNEMENT => 'DYS',
        };
    }
}
