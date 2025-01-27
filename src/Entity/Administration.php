<?php

namespace MonIndemnisationJustice\Entity;

enum Administration: string
{
    case MINISTERE_JUSTICE = 'MJ';
    case POLICE_NATIONALE = 'PN';
    case GENDARMERIE_NATIONALE = 'GN';

    public function estRattachee(array $domaines): bool
    {
        return match ($this) {
            self::MINISTERE_JUSTICE => in_array('justice.gouv.fr', $domaines),
            self::POLICE_NATIONALE => in_array('interieur.gouv.fr', $domaines),
            self::GENDARMERIE_NATIONALE => in_array('gendarmerie.interieur.gouv.fr', $domaines),
        };
    }

    public function estAutoValide(): bool
    {
        return !empty($this->getRoles());
    }

    public function getRoles(): array
    {
        return match ($this) {
            self::POLICE_NATIONALE, self::GENDARMERIE_NATIONALE => [Agent::ROLE_AGENT_FORCES_DE_L_ORDRE],
            default => [],
        };
    }

    public static function fromDomaines(array $domaines): ?Administration
    {
        foreach (self::cases() as $administration) {
            if ($administration->estRattachee($domaines)) {
                return $administration;
            }
        }

        return null;
    }
}
