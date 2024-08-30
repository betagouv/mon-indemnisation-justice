<?php
namespace App\Security\Voter;

use App\Entity\Agent;
use App\Entity\Requerant;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Contracts\PrejudiceInterface;
use App\Contracts\VoterInterface;

trait PrejudiceTrait {
  private function isPrejudice(mixed $subject): bool
  {
    return ($subject instanceof PrejudiceInterface);
  }

  private function canValidOrReject(string $attribute, mixed $subject, UserInterface $user): bool
  {
    return
    ($attribute === VoterInterface::PREJUDICE_VALID_OR_REJECT) &&
    (
      $user->hasRole(Agent::ROLE_AGENT_REDACTEUR)
      ||
      $user->hasRole(Agent::ROLE_AGENT_VALIDATEUR)
    );
  }

  private function canView(string $attribute, mixed $subject, UserInterface $user): bool
  {
    return
      ($attribute === VoterInterface::PREJUDICE_VIEW) &&
      (
        ($user->hasRole(Requerant::ROLE_REQUERANT) && ($subject->getRequerant() === $user))
        ||
        $user->hasRole(Agent::ROLE_AGENT_REDACTEUR)
        ||
        $user->hasRole(Agent::ROLE_AGENT_VALIDATEUR)
      )
    ;
  }

  private function canEdit(string $attribute, mixed $subject, UserInterface $user): bool
  {
    return
      ($attribute === VoterInterface::PREJUDICE_EDIT) &&
      (
        ($user->hasRole(Requerant::ROLE_REQUERANT) && ($subject->getRequerant() === $user))
        ||
        $user->hasRole(Agent::ROLE_AGENT_REDACTEUR)
        ||
        $user->hasRole(Agent::ROLE_AGENT_VALIDATEUR)
      )
    ;
  }
}
