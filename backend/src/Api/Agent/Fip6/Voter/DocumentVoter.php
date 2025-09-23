<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Voter;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Document;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DocumentVoter extends Voter
{
    public const ACTION_IMPRIMER = 'document:imprimer';
    public const ACTION_RENSEIGNER = 'document:renseigner';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ACTION_IMPRIMER, self::ACTION_RENSEIGNER]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$token->getUser() instanceof Agent) {
            return false;
        }

        /** @var Agent $agent */
        $agent = $token->getUser();

        return match ($attribute) {
            self::ACTION_IMPRIMER => $this->agentPeutImprimer($agent, $subject),
            self::ACTION_RENSEIGNER => $this->agentPeutRenseigner($agent),
            default => false
        };
    }

    protected function agentPeutImprimer(Agent $agent, Document $document): bool
    {
        return $agent->hasRole(Agent::ROLE_AGENT_VALIDATEUR)
            || $agent->instruit($document->getDossier());
    }

    protected function agentPeutRenseigner(Agent $agent): bool
    {
        return $agent->aRole(Agent::ROLE_AGENT_DOSSIER);
    }
}
