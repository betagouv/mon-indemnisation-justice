<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Voter;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DeclarationFDOBrisPorteVoter extends Voter
{
    public const ACTION_LISTER = 'declaration:lister';
    public const ACTION_DECLARER = 'declaration:declarer';
    public const ACTION_SUPPRIMER = 'declaration:supprimer';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ACTION_LISTER, self::ACTION_DECLARER, self::ACTION_SUPPRIMER]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$token->getUser() instanceof Agent) {
            return false;
        }

        /** @var Agent $agent */
        $agent = $token->getUser();

        if (self::ACTION_SUPPRIMER === $attribute) {
            if ($subject instanceof BrouillonDeclarationFDOBrisPorte) {
                $brouillon = $subject;

                return $agent->aRole(Agent::ROLE_AGENT_FORCES_DE_L_ORDRE) && $brouillon->getAgent() === $agent;
            }

            return false;
        }

        return $agent->aRole(Agent::ROLE_AGENT_FORCES_DE_L_ORDRE);
    }
}
