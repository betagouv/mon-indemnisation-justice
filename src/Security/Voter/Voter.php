<?php

namespace MonIndemnisationJustice\Security\Voter;

use MonIndemnisationJustice\Contracts\VoterInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Requerant;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as SfVoter;
use Symfony\Component\Security\Core\User\UserInterface;

class Voter extends SfVoter
{
    public const EDIT = 'edit';
    public const VIEW = 'view';
    public const PREJUDICE_VALID_OR_REJECT = 'prejudice_valid_or_reject';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            VoterInterface::PREJUDICE_VIEW,
            VoterInterface::PREJUDICE_EDIT,
            VoterInterface::PREJUDICE_VALID_OR_REJECT,
        ]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject instanceof BrisPorte) {
            if (
                $this->canView(attribute: $attribute, subject: $subject, user: $user)
                || $this->canEdit(attribute: $attribute, subject: $subject, user: $user)
                || $this->canValidOrReject(attribute: $attribute, subject: $subject, user: $user)
            ) {
                return true;
            } else {
                throw new \LogicException('Privilèges insuffisants pour accéder à la ressource.');
            }
        }

        return true;
    }

    protected function canValidOrReject(string $attribute, mixed $subject, UserInterface $user): bool
    {
        return
        (VoterInterface::PREJUDICE_VALID_OR_REJECT === $attribute)
        && (
            $user->hasRole(Agent::ROLE_AGENT_REDACTEUR)
            || $user->hasRole(Agent::ROLE_AGENT_VALIDATEUR)
        );
    }

    protected function canView(string $attribute, mixed $subject, UserInterface $user): bool
    {
        return
          (VoterInterface::PREJUDICE_VIEW === $attribute)
          && (
              ($user->hasRole(Requerant::ROLE_REQUERANT) && ($subject->getRequerant() === $user))
              || $user->hasRole(Agent::ROLE_AGENT_REDACTEUR)
              || $user->hasRole(Agent::ROLE_AGENT_VALIDATEUR)
          )
        ;
    }

    protected function canEdit(string $attribute, mixed $subject, UserInterface $user): bool
    {
        return
          (VoterInterface::PREJUDICE_EDIT === $attribute)
          && (
              ($user->hasRole(Requerant::ROLE_REQUERANT) && ($subject->getRequerant() === $user))
              || $user->hasRole(Agent::ROLE_AGENT_REDACTEUR)
              || $user->hasRole(Agent::ROLE_AGENT_VALIDATEUR)
          )
        ;
    }
}
