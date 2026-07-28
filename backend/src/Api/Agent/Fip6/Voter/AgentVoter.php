<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Voter;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AgentVoter extends Voter
{
    public const ACTION_S_IDENTIFIER = 'agent:s-identifier';
    public const ACTION_LISTER_REDACTEURS = 'agent:lister-redacteurs';
    public const ACTION_RECHERCHER = 'agent:rechercher';
    public const ACTION_CREER = 'agent:creer';
    public const ACTION_EDITER = 'agent:editer';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ACTION_S_IDENTIFIER, self::ACTION_LISTER_REDACTEURS, self::ACTION_RECHERCHER, self::ACTION_CREER, self::ACTION_EDITER]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$token->getUser() instanceof Agent) {
            return false;
        }

        /** @var Agent $agent */
        $agent = $token->getUser();

        return match ($attribute) {
            self::ACTION_S_IDENTIFIER => $agent->estMinistereJustice(),
            self::ACTION_LISTER_REDACTEURS => $agent->aRole(Agent::ROLE_AGENT_DOSSIER),
            default => $this->peutGererAgents($agent),
        };
    }

    protected function peutGererAgents(Agent $agent): bool
    {
        return $agent->aRole(Agent::ROLE_AGENT_GESTION_PERSONNEL);
    }
}
