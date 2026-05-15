<?php

namespace MonIndemnisationJustice\Entity;

enum AdministrationType: string
{
    case MINISTERE_JUSTICE = 'MJ';
    case POLICE_NATIONALE = 'PN';
    case PREFECTURE_DE_POLICE = 'PP';
    case GENDARMERIE_NATIONALE = 'GN';


    public const string SIRET_MJ = '11001001400014';
    public const string SIRET_PN = '12001501100014';
    public const string SIRET_PP = '17750151700011';
    public const string SIRET_GN = '15700001900461';

    public function estRattachee(array $domaines): bool
    {
        return match ($this) {
            self::MINISTERE_JUSTICE => in_array('justice.gouv.fr', $domaines),
            self::POLICE_NATIONALE => in_array('interieur.gouv.fr', $domaines),
            self::GENDARMERIE_NATIONALE => in_array('gendarmerie.interieur.gouv.fr', $domaines),
        };
    }

    public function getLibelle(): string
    {
        return match ($this) {
            self::MINISTERE_JUSTICE => 'Ministère de la Justice',
            self::POLICE_NATIONALE => 'Police Nationale',
            self::GENDARMERIE_NATIONALE => 'Gendarmerie Nationale',
        };
    }

    public function estLibelleFeminin(): bool
    {
        return match ($this) {
            self::MINISTERE_JUSTICE => false,
            self::POLICE_NATIONALE, self::GENDARMERIE_NATIONALE => true,
        };
    }

    public function getRolesEligibles(): array
    {
        return match ($this) {
            self::MINISTERE_JUSTICE => [
                Agent::ROLE_AGENT_REDACTEUR,
                Agent::ROLE_AGENT_DOSSIER,
                Agent::ROLE_AGENT_ATTRIBUTEUR,
                Agent::ROLE_AGENT_VALIDATEUR,
                Agent::ROLE_AGENT_GESTION_PERSONNEL,
                Agent::ROLE_AGENT_LIAISON_BUDGET,
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

    public function estFDO(): bool
    {
        return self::POLICE_NATIONALE === $this || self::GENDARMERIE_NATIONALE === $this;
    }

    public function estMinistereJustice(): bool
    {
        return self::MINISTERE_JUSTICE === $this;
    }

    /**
     * Liste des rôles automatiquement octroyés à la première connexion.
     */
    public function getRolesAutomatiques(): array
    {
        return match ($this) {
            self::POLICE_NATIONALE, self::GENDARMERIE_NATIONALE => [Agent::ROLE_AGENT_FORCES_DE_L_ORDRE],
            default => [],
        };
    }

    public static function fromDomaines(array $domaines): ?AdministrationType
    {
        foreach (self::cases() as $administration) {
            if ($administration->estRattachee($domaines)) {
                return $administration;
            }
        }

        return null;
    }
}
