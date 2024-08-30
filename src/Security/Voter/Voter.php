<?php
namespace App\Security\Voter;

use App\Contracts\VoterInterface;
use App\Entity\Requerant;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Contracts\PrejudiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as SfVoter;
use Symfony\Contracts\Translation\TranslatorInterface;

class Voter extends SfVoter
{
  const EDIT = 'edit';
  const VIEW = 'view';
  const PREJUDICE_VALID_OR_REJECT = 'prejudice_valid_or_reject';

  use PrejudiceTrait;

  public function __construct(
    private TranslatorInterface $translator
  )
  {

  }

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
          throw new \LogicException($this->translator->trans('Not privileged to request the resource.',domain: 'security'));
      }

      return true;
  }
}
