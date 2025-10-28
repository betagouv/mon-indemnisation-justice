<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Voter;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DeclarationErreurOperationelleVoter extends Voter
{
    public const ACTION_LISTER = 'declaration:lister';
    public const ACTION_DECLARER = 'declaration:declarer';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ACTION_LISTER, self::ACTION_DECLARER]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$token->getUser() instanceof Agent) {
            return false;
        }

        /** @var Agent $agent */
        $agent = $token->getUser();

        return $agent->aRole(Agent::ROLE_AGENT_FORCES_DE_L_ORDRE);
    }
}
