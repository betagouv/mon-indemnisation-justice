<?php

namespace MonIndemnisationJustice\Security\Provider;

use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\Security\UserProvider\OidcUserProviderInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class AgentProvider implements OidcUserProviderInterface
{
    public function __construct(protected readonly AgentRepository $agentRepository)
    {
    }

    public function ensureUserExists(string $userIdentifier, OidcUserData $userData)
    {
        //dd($userIdentifier, $userData);
    }

    public function loadOidcUser(string $userIdentifier): Agent
    {
        return $this->loadUserByIdentifier($userIdentifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return Agent::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): Agent
    {
        //dd($identifier);
        return $this->agentRepository->findOneBy(['email' => $identifier]);
    }
}
