<?php

namespace MonIndemnisationJustice\Security\Voter;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Document;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FDODocumentVoter extends Voter
{
    public const ACTION_CONSULTER = 'document:consulter';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ACTION_CONSULTER]) && $subject instanceof Document;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$subject instanceof Document) {
            return false;
        }

        /** @var Document $pieceJointe */
        $pieceJointe = $subject;
        if (!$token->getUser() instanceof Agent) {
            return false;
        }

        /** @var Agent $agent */
        $agent = $token->getUser();

        return $this->agentPeutConsulter($pieceJointe, $agent);
    }

    protected function agentPeutConsulter(Document $pieceJointe, Agent $agent): bool
    {
        return $agent->estFDO()
            && $pieceJointe->getDeclarations()->exists(fn (int $clef, DeclarationFDOBrisPorte $declaration) => $declaration->getAgent() == $agent)
            // TODO : trouver un moyen de vérifier que la pièce jointe émane bien de l'agent
            || true;
    }
}
