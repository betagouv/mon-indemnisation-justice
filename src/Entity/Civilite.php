<?php

namespace MonIndemnisationJustice\Entity;

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

    public function estFeminin(): bool
    {
        return match ($this) {
            Civilite::M => false,
            Civilite::MME => true,
        };
    }

    public function libelleNaissance(string $nomNaissance): string
    {
        return sprintf('né%s %s', $this->estFeminin() ? 'e' : '', $nomNaissance);
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
