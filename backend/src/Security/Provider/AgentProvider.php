<?php

namespace MonIndemnisationJustice\Security\Provider;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AgentProvider implements UserProviderInterface
{
    public function __construct(protected readonly AgentRepository $agentRepository)
    {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Agent) {
            throw new UnsupportedUserException($user::class);
        }

        $agent = $this->agentRepository->findOneBy(['identifiant' => $user->getIdentifiant()]);

        if (null === $agent) {
            throw new UserNotFoundException($user->getIdentifiant());
        }

        return $agent;
    }

    public function supportsClass(string $class): bool
    {
        return Agent::class === $class;
    }

    public function loadUserByIdentifier(?string $identifier): Agent
    {
        if (null === $identifier) {
            throw new UserNotFoundException('Missing identifier parameter');
        }

        $agent = $this->agentRepository->findOneBy(['identifiant' => $identifier]);

        if (null === $agent) {
            throw new UserNotFoundException("No agent found for identifier $identifier");
        }

        return $agent;
    }
}
