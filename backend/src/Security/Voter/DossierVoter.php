<?php

namespace MonIndemnisationJustice\Security\Voter;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DossierVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!str_starts_with($attribute, 'lister:dossiers:')) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$token->getUser() instanceof Agent) {
            return false;
        }

        $liste = explode(':', $attribute)[2] ?? null;

        /** @var Agent $agent */
        $agent = $token->getUser();

        if (in_array($liste, ['a-transmettre', 'en-attente-indemnisation'])) {
            return $agent->hasRole(Agent::ROLE_AGENT_LIAISON_BUDGET);
        }

        if (in_array($liste, ['a-attribuer'])) {
            return $agent->hasRole(Agent::ROLE_AGENT_ATTRIBUTEUR);
        }

        return false;
    }
}
