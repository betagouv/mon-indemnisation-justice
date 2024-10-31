<?php

namespace App\Entity;

enum Civilite: string
{
    case M = 'M';
    case MME = 'MME';

    public function getLibelle(): string
    {
        return match ($this) {
            Civilite::M => 'Monsieur',
            Civilite::MME => 'Madame',
        };
    }

    public static function tryFromName(?string $name): ?Civilite
    {
        if (null === $name) {
            return null;
        }

        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }

    public static function choices(): array
    {
        return array_merge(
            ...array_map(
                function (Civilite $civilite) {
                    return [$civilite->value => $civilite];
                },
                self::cases()
            )
        );
    }
}
