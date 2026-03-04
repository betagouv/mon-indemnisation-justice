<?php

namespace MonIndemnisationJustice\Api\Requerant\Voter;

use MonIndemnisationJustice\Entity\Brouillon;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RequerantBrouillonVoter extends Voter
{
    public const string ACTION_BROUILLON_INITIER = 'brouillon:initier';
    public const string ACTION_BROUILLON_AMENDER = 'brouillon:amender';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array(
            $attribute,
            [
                self::ACTION_BROUILLON_INITIER,
                self::ACTION_BROUILLON_AMENDER,
            ]
        );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$token->getUser() instanceof Usager) {
            return false;
        }

        /** @var Usager $usager */
        $usager = $token->getUser();

        if (self::ACTION_BROUILLON_INITIER == $attribute) {
            return true;
        }

        // L'action d'édition est liée à un brouillon spécifique
        if (!($brouillon = $subject) instanceof Brouillon) {
            return false;
        }

        return $brouillon->getUsager() === $usager;
    }
}
