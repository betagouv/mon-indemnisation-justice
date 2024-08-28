<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RequerantChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (false === $user->isVerified()) {
            throw new CustomUserMessageAuthenticationException('Ce compte n\'est pas vérifié.');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
    }
}
