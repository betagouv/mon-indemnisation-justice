<?php

namespace MonIndemnisationJustice\Security\Voter;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Document;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DocumentVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if ('imprimer' !== $attribute) {
            return false;
        }

        if (!$subject instanceof Document) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$token->getUser() instanceof Agent) {
            return false;
        }

        /** @var Agent $agent */
        $agent = $token->getUser();

        /** @var Document $document */
        $document = $subject;

        return $agent->hasRole(Agent::ROLE_AGENT_VALIDATEUR)
            || $agent->instruit($document->getDossier());
    }
}
