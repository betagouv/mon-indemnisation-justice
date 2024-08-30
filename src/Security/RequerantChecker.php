<?php

namespace App\Security;

use App\Entity\Requerant;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RequerantChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof Requerant && !$user->estVerifieCourriel()) {
            throw new CustomUserMessageAuthenticationException('Ce compte n\'est pas vérifié.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
