<?php
namespace App\Security\Voter;

use App\Entity\User;
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
      $user->hasRole(User::ROLE_REDACTEUR_PRECONTENTIEUX)
      ||
      $user->hasRole(User::ROLE_CHEF_PRECONTENTIEUX)
    );
  }

  private function canView(string $attribute, mixed $subject, UserInterface $user): bool
  {
    return
      ($attribute === VoterInterface::PREJUDICE_VIEW) &&
      (
        ($user->hasRole(User::ROLE_REQUERANT) && ($subject->getRequerant() === $user))
        ||
        $user->hasRole(User::ROLE_REDACTEUR_PRECONTENTIEUX)
        ||
        $user->hasRole(User::ROLE_CHEF_PRECONTENTIEUX)
      )
    ;
  }

  private function canEdit(string $attribute, mixed $subject, UserInterface $user): bool
  {
    return
      ($attribute === VoterInterface::PREJUDICE_EDIT) &&
      (
        ($user->hasRole(User::ROLE_REQUERANT) && ($subject->getRequerant() === $user))
        ||
        $user->hasRole(User::ROLE_REDACTEUR_PRECONTENTIEUX)
        ||
        $user->hasRole(User::ROLE_CHEF_PRECONTENTIEUX)
      )
    ;
  }
}
