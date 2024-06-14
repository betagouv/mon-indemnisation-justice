<?php
namespace App\Security\Voter;

use App\Entity\User;
use App\Contracts\PrejudiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as SfVoter;
use Symfony\Contracts\Translation\TranslatorInterface;

class Voter extends SfVoter
{
  const EDIT = 'edit';
  const VIEW = 'view';

  public function __construct(
    private TranslatorInterface $translator
  )
  {

  }

  protected function supports(string $attribute, mixed $subject): bool
  {
      if (!in_array($attribute, [self::VIEW, self::EDIT]))
          return false;
      return true;
  }

  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
  {
      $user = $token->getUser();

      if(!$user instanceof User)
        return false;

      if($subject instanceof PrejudiceInterface)
      {
        if(
          (
            $user->hasRole(User::ROLE_REQUERANT) &&
            ($subject->getRequerant() === $user)
          )
          ||
          $user->hasRole(User::ROLE_REDACTEUR_PRECONTENTIEUX)
          ||
          $user->hasRole(User::ROLE_CHEF_PRECONTENTIEUX)
          )
        {
          return true;
        }
        else
          throw new \LogicException($this->translator->trans('Not privileged to request the resource.',domain: 'security'));
      }
      
      return true;
  }
}
