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

    public static function detecterDepuisSource(mixed $source): ?self
    {
        $class = get_class($source);

        if (!$class) {
            return null;
        }

        return array_find(self::cases(), fn (BrouillonType $type) => $type->getEntiteCible() === $class);
    }
}
