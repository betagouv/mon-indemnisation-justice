<?php

namespace MonIndemnisationJustice\Security;

use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UsagerChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof Usager && !$user->estVerifieCourriel()) {
            throw new CustomUserMessageAuthenticationException('Ce compte n\'est pas vérifié.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
