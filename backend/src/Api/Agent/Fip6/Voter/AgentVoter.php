<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Voter;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AgentVoter extends Voter
{
    public const ACTION_LISTER = 'agent:lister';
    public const ACTION_CREER = 'agent:creer';
    public const ACTION_EDITER = 'agent:editer';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ACTION_LISTER, self::ACTION_CREER, self::ACTION_EDITER]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$token->getUser() instanceof Agent) {
            return false;
        }

        /** @var Agent $agent */
        $agent = $token->getUser();

        return $agent->aRole(Agent::ROLE_AGENT_GESTION_PERSONNEL);
    }
}
