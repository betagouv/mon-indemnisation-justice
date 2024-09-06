<?php
namespace App\Security\Voter;

use App\Contracts\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as SfVoter;

class Voter extends SfVoter
{
  const EDIT = 'edit';
  const VIEW = 'view';
  const PREJUDICE_VALID_OR_REJECT = 'prejudice_valid_or_reject';

  use PrejudiceTrait;

  protected function supports(string $attribute, mixed $subject): bool
  {
      return in_array($attribute, [
        VoterInterface::PREJUDICE_VIEW,
        VoterInterface::PREJUDICE_EDIT,
        VoterInterface::PREJUDICE_VALID_OR_REJECT
      ]);
  }

  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
  {
      $user = $token->getUser();

      if(!$user instanceof UserInterface)
        return false;

      if(true === $this->isPrejudice(subject: $subject))
      {
        if(
          $this->canView(attribute:$attribute,subject:$subject,user:$user) ||
          $this->canEdit(attribute:$attribute,subject:$subject,user:$user) ||
          $this->canValidOrReject(attribute:$attribute,subject:$subject,user:$user)
        )
          return true;
        else
          throw new \LogicException("Privilèges insuffisants pour accéder à la ressource.");
      }

      return true;
  }
}
