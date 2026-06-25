<?php

namespace MonIndemnisationJustice\Api\Requerant\Voter;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RequerantDossierVoter extends Voter
{
    public const string ACTION_DOSSIER_LISTER = 'dossier:lister';
    public const string ACTION_DOSSIER_INITIER = 'dossier:initier';
    public const string ACTION_DOSSIER_CONSULTER = 'dossier:consulter';
    public const string ACTION_DOSSIER_AMENDER = 'dossier:amender';
    public const string ACTION_DOSSIER_TELEVERSER = 'dossier:televerser';
    public const string ACTION_DOSSIER_SUPPRIMER_PIECE_JOINTE = 'dossier:supprimer-piece-jointe';
    public const string ACTION_DOSSIER_PUBLIER = 'dossier:publier';
    public const string ACTION_DOSSIER_ACCEPTER_INDEMNISATION = 'dossier:accepter_indemnisation';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array(
            $attribute,
            [
                self::ACTION_DOSSIER_LISTER,
                self::ACTION_DOSSIER_INITIER,
                self::ACTION_DOSSIER_CONSULTER,
                self::ACTION_DOSSIER_AMENDER,
                self::ACTION_DOSSIER_TELEVERSER,
                self::ACTION_DOSSIER_SUPPRIMER_PIECE_JOINTE,
                self::ACTION_DOSSIER_PUBLIER,
                self::ACTION_DOSSIER_ACCEPTER_INDEMNISATION,
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

        if (in_array($attribute, [self::ACTION_DOSSIER_LISTER, self::ACTION_DOSSIER_INITIER])) {
            return true;
        }

        if (self::ACTION_DOSSIER_SUPPRIMER_PIECE_JOINTE === $attribute) {
            if (!$subject instanceof Document) {
                return false;
            }
            /** @var Document $pieceJointe */
            $pieceJointe = $subject;

            // La pièce jointe est lié au dossier du requérant
            return $pieceJointe->getDossier()->getUsager() === $usager;
        }

        // Les actions de consultation / édition, liée à un dossier spécifique, sont restreintes à l'usager l'ayant initié
        if (!($dossier = $subject) instanceof Dossier) {
            return false;
        }

        // En tant qu'usager, je peux consulter, amender et publier seulement les dossiers que j'ai initiés.
        // Note: c'est ici qu'on fera évoluer la règle avec les personnes morale.
        return $dossier->getUsager() === $usager;
    }
}
