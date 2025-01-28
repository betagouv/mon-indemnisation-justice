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

    public function getRolesEligibles(): array
    {
        return match ($this) {
            self::MINISTERE_JUSTICE => [
                Agent::ROLE_AGENT_VALIDATEUR,
                Agent::ROLE_AGENT_ATTRIBUTEUR,
                Agent::ROLE_AGENT_REDACTEUR,
                Agent::ROLE_AGENT_GESTION_PERSONNEL,
                Agent::ROLE_AGENT_BUREAU_BUDGET,
            ],
            self::POLICE_NATIONALE, self::GENDARMERIE_NATIONALE => [Agent::ROLE_AGENT_FORCES_DE_L_ORDRE],
        };
    }

    /**
     * Indique si un compte agent relié à cette administration est automatiquement validé. Et donc ne nécessité pas de
     * validation de la part d'un agent disposant du rôle `ROLE_AGENT_GESTION_PERSONNEL`.
     */
    public function estAutoValide(): bool
    {
        return !empty($this->getRolesAutomatiques());
    }

    /**
     * Liste des roles automatiquement octroyés à la première connexion.
     */
    public function getRolesAutomatiques(): array
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

    public static function getRolesParAdministration(): array
    {
        return array_merge(
            ...array_map(
                fn (Administration $administration) => [
                    $administration->value => $administration->getRolesEligibles(),
                ],
                self::cases()
            )
        );
    }
}
