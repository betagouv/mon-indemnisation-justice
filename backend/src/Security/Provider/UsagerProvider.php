<?php

namespace MonIndemnisationJustice\Security\Provider;

use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Repository\UsagerRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UsagerProvider implements UserProviderInterface
{
    public function __construct(protected readonly UsagerRepository $usagerRepository)
    {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Usager) {
            throw new UnsupportedUserException($user::class);
        }

        $usager = $this->usagerRepository->findOneBy(['email' => $user->getEmail()]);

        if (null === $usager) {
            throw new UserNotFoundException($user->getEmail());
        }

        return $usager;
    }

    public function supportsClass(string $class): bool
    {
        return Usager::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): Usager
    {
        $email = strtolower($identifier);

        $usager = $this->usagerRepository->findOneBy(['email' => $email]);

        if (null === $usager) {
            throw new UserNotFoundException("Aucun compte associé avec l'adresse courriel $email");
        }

        return $usager;
    }
}
