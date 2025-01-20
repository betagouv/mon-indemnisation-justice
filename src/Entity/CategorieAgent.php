<?php

namespace MonIndemnisationJustice\Entity;

enum CategorieAgent: string
{
    case AGENT_BUREAU_PRECONTENTIEUX = 'AGENT_BUREAU_PRECONTENTIEUX';
    case AGENT_BUREAU_BUDGET = 'AGENT_BUREAU_BUDGET';

    case AGENT_FORCES_DE_L_ORDRE = 'AGENT_FORCES_DE_L_ORDRE';

    case AGENT_AUTRE = 'AGENT_AUTRE';

    public function estMinistereJustice(): bool
    {
        return match ($this) {
            self::AGENT_BUREAU_PRECONTENTIEUX, self::AGENT_BUREAU_BUDGET => true,
            default => false,
        };
    }

    public function estAutoValide(): bool
    {
        return !$this->estMinistereJustice();
    }
}
