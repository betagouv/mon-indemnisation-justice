<?php
namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
  public function checkPreAuth(UserInterface $user)
  {
    if (false === $user->isVerified()) {
        throw new CustomUserMessageAuthenticationException('Account is not verified.');
    }
  }
  public function checkPostAuth(UserInterface $user)
    {

    }
}
