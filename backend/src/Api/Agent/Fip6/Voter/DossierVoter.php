<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Voter;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DossierVoter extends Voter
{
    public const ACTION_ATTRIBUER = 'dossier:attribuer';
    public const ACTION_INSTRUIRE = 'dossier:instruire';

    public const ACTION_AJOUTER_PIECE_JOINTE = 'dossier:ajouter-piece-jointe';
    public const ACTION_GENERER_DOCUMENT = 'dossier:generer-document';

    public const ACTION_LISTER_A_CATEGORISER = 'dossier:lister:a-categoriser';
    public const ACTION_LISTER_A_ATTRIBUER = 'dossier:lister:a-attribuer';
    public const ACTION_LISTER_A_INSTRUIRE = 'dossier:lister:a-instruire';

    public const ACTION_LISTER_REJET_A_SIGNER = 'dossier:lister:rejet-a-signer';
    public const ACTION_LISTER_PROPOSITION_A_SIGNER = 'dossier:lister:proposition-a-signer';

    public const ACTION_LISTER_A_VERIFIER = 'dossier:lister:a-verifier';
    public const ACTION_LISTER_ARRETE_A_SIGNER = 'dossier:lister:arrete-a-signer';
    public const ACTION_LISTER_A_TRANSMETTRE = 'dossier:lister:a-transmettre';
    public const ACTION_LISTER_EN_ATTENTE_INDEMNISATION = 'dossier:lister:a-transmettre';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::ACTION_ATTRIBUER,
            self::ACTION_INSTRUIRE,
            self::ACTION_AJOUTER_PIECE_JOINTE,
            self::ACTION_GENERER_DOCUMENT,
            self::ACTION_LISTER_A_CATEGORISER,
            self::ACTION_LISTER_A_ATTRIBUER,
            self::ACTION_LISTER_A_INSTRUIRE,
            self::ACTION_LISTER_REJET_A_SIGNER,
            self::ACTION_LISTER_PROPOSITION_A_SIGNER,
            self::ACTION_LISTER_ARRETE_A_SIGNER,
            self::ACTION_LISTER_A_VERIFIER,
            self::ACTION_LISTER_ARRETE_A_SIGNER,
            self::ACTION_LISTER_A_TRANSMETTRE,
            self::ACTION_LISTER_EN_ATTENTE_INDEMNISATION,
        ]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$token->getUser() instanceof Agent) {
            return false;
        }

        /** @var Agent $agent */
        $agent = $token->getUser();

        return match ($attribute) {
            self::ACTION_AJOUTER_PIECE_JOINTE, => $this->agentPeutAjouterPieceJointe($agent, $subject),
            self::ACTION_ATTRIBUER => $this->agentPeutAttribuer($agent),
            self::ACTION_INSTRUIRE => $this->agentPeutInstruire($agent, $subject),
            self::ACTION_GENERER_DOCUMENT, => $this->agentPeutGenererDocument($agent, $subject),
            self::ACTION_LISTER_A_CATEGORISER, self::ACTION_LISTER_A_ATTRIBUER, self::ACTION_LISTER_A_INSTRUIRE, self::ACTION_LISTER_REJET_A_SIGNER, self::ACTION_LISTER_PROPOSITION_A_SIGNER, self::ACTION_LISTER_A_VERIFIER, self::ACTION_LISTER_ARRETE_A_SIGNER, self::ACTION_LISTER_A_TRANSMETTRE, self::ACTION_LISTER_EN_ATTENTE_INDEMNISATION => $this->agentPeutLister($agent, $attribute),
            default => false
        };
    }

    protected function agentPeutAjouterPieceJointe(Agent $agent, BrisPorte $dossier): bool
    {
        return $agent->aRole(Agent::ROLE_AGENT_VALIDATEUR) || ($agent->estRedacteur() && $agent->instruit($dossier));
    }

    protected function agentPeutAttribuer(Agent $agent): bool
    {
        return $agent->hasRole(Agent::ROLE_AGENT_ATTRIBUTEUR);
    }

    protected function agentPeutInstruire(Agent $agent, BrisPorte $dossier): bool
    {
        return $agent->estRedacteur() && $agent->instruit($dossier);
    }

    protected function agentPeutGenererDocument(Agent $agent, BrisPorte $dossier): bool
    {
        return ($agent->estRedacteur() && $agent->instruit($dossier)) || $agent->aRole(Agent::ROLE_AGENT_VALIDATEUR);
    }

    protected function agentPeutLister(Agent $agent, string $action): bool
    {
        return match ($action) {
            self::ACTION_LISTER_A_CATEGORISER => $agent->aRole(Agent::ROLE_AGENT_BETAGOUV),
            self::ACTION_LISTER_A_ATTRIBUER => $agent->aRole(Agent::ROLE_AGENT_ATTRIBUTEUR),
            self::ACTION_LISTER_A_INSTRUIRE, self::ACTION_LISTER_A_VERIFIER => $agent->aRole(Agent::ROLE_AGENT_REDACTEUR),
            self::ACTION_LISTER_REJET_A_SIGNER, self::ACTION_LISTER_PROPOSITION_A_SIGNER, self::ACTION_LISTER_ARRETE_A_SIGNER => $agent->aRole(Agent::ROLE_AGENT_VALIDATEUR),
            self::ACTION_LISTER_A_TRANSMETTRE, self::ACTION_LISTER_EN_ATTENTE_INDEMNISATION => $agent->aRole(Agent::ROLE_AGENT_LIAISON_BUDGET),
            default => false
        };
    }
}
