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
    public const ACTION_EDITER = 'declaration:editer';
    public const ACTION_SUPPRIMER = 'declaration:supprimer';
    public const ACTION_SOUMETTRE = 'declaration:soumettre';
    public const ACTION_AJOUTER_PJ = 'declaration:ajouter-pj';
    public const ACTION_SUPPRIMER_PJ = 'declaration:supprimer-pj';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array(
            $attribute,
            [
                self::ACTION_LISTER,
                self::ACTION_DECLARER,
                self::ACTION_EDITER,
                self::ACTION_SOUMETTRE,
                self::ACTION_SUPPRIMER,
                self::ACTION_AJOUTER_PJ,
                self::ACTION_SUPPRIMER_PJ,
            ]
        );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$token->getUser() instanceof Agent) {
            return false;
        }

        /** @var Agent $agent */
        $agent = $token->getUser();

        if (!$agent->aRole(Agent::ROLE_AGENT_FORCES_DE_L_ORDRE)) {
            return false;
        }

        if (in_array($attribute, [self::ACTION_LISTER, self::ACTION_DECLARER])) {
            return true;
        }

        // Les actions d'édition / suppression / soumissions / ajout PJ / suppression PJ sont liées à un brouillon
        if (!$subject instanceof BrouillonDeclarationFDOBrisPorte) {
            return false;
        }

        $brouillon = $subject;

        return $brouillon->getAgent() === $agent;
    }
}
