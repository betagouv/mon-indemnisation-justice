<?php

namespace MonIndemnisationJustice\Security;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AgentChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Agent) {
            throw new CustomUserMessageAuthenticationException("Ce compte ne semble pas lié à un agent");
        } else if (!$user->isActive()) {
            throw new CustomUserMessageAuthenticationException("Ce compte agent a été désactivé");
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
