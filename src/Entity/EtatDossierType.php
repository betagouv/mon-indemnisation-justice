<?php

namespace MonIndemnisationJustice\Entity;

enum EtatDossierType: string
{
    // Le requérant a initié un dossier qu'il n'a pas encore déposé
    case DOSSIER_A_FINALISER = 'A_FINALISER';
    // Le requérant a finalisé et déposé son dossier
    case DOSSIER_A_INSTRUIRE = 'A_INSTRUIRE';
    case DOSSIER_EN_INSTRUCTION = 'EN_INSTRUCTION';

    // Le dossier existait d'abord en version papier
    case DOSSIER_CLOTURE = 'CLOTURE';

    // Le rédacteur a approuvé l'indemnisation
    case DOSSIER_OK_A_SIGNER = 'OK_A_SIGNER';

    // Le validateur a signé le courrier d'indemnisation, c'est au tour du requérant d'approuver
    case DOSSIER_OK_A_APPROUVER = 'OK_A_APPROUVER';

    // Le requérant a accepté la proposition d'indemnisation, le rédacteur doit vérifier la déclaration d'acceptation
    case DOSSIER_OK_A_VERIFIER = 'OK_A_VERIFIER';

    // La déclaration d'acceptation est validée par le rédacteur, l'agent validateur doit générer l'arrêté de paiement
    case DOSSIER_OK_VERIFIE = 'OK_VERIFIE';

    // L'arrêté de paiement est généré, l'agent "transmission budget" doit le télécharger et le transmettre au bureau du
    // budget.
    case DOSSIER_OK_A_INDEMNISER = 'OK_A_INDEMNISER';

    // L'agent "transmission budget" a transmis l'arrêté de paiement au bureau du budget et attend un retour de leur
    // part.
    case DOSSIER_OK_EN_ATTENTE_PAIEMENT = 'OK_EN_ATTENTE_PAIEMENT';

    // L'agent "transmission budget" a été notifiée du versement de l'indemnisation, elle déclare le dossier comme
    // indemnisé et clos.
    case DOSSIER_OK_INDEMNISE = 'OK_INDEMNISE';

    case DOSSIER_KO_A_SIGNER = 'KO_A_SIGNER';
    case DOSSIER_KO_REJETE = 'KO_REJETE';

    public function slugAction(): string
    {
        return preg_replace('/_/', '-', strtolower($this->value));
    }

    public function getLibelle(): string
    {
        return match ($this) {
            self::DOSSIER_A_FINALISER => "Demande d'indemnisation en cours de constitution",
            self::DOSSIER_A_INSTRUIRE => "Demande d'indemnisation déposée",
            self::DOSSIER_OK_A_SIGNER => "Demande d'indemnisation validée (en attente de signature)",
            self::DOSSIER_KO_A_SIGNER => "Demande d'indemnisation rejetée (en attente de signature)",
            default => $this->value,
            /*
            self::RENVOI_EN_CONSTITUTION => "Demande de pièce(s) complémentaire(s) sur la demande d'indemnisation",
            self::VALIDE => "Demande d'indemnisation validée (en attente signature)",
            self::REJETE => "Demande d'indemnisation rejetée (en attente signature)",
            self::SIGNATURE_VALIDEE => "Demande d'indemnisation validée",
            self::SIGNATURE_REJETEE => "Demande d'indemnisation rejetée",
            self::ACCORD_OFFRE => "Proposition d'indemnisation acceptée",
            self::REFUS_OFFRE => "Proposition d'indemnisation rejetée",
            */
        };
    }

    public function estASigner(): bool
    {
        return str_ends_with($this->value, 'A_SIGNER');
    }

    public function estDecide(): bool
    {
        return $this->estASigner();
    }

    public function estSigne(): bool
    {
        return in_array($this, [
            self::DOSSIER_OK_A_APPROUVER, self::DOSSIER_OK_A_VERIFIER, self::DOSSIER_OK_A_INDEMNISER, self::DOSSIER_OK_INDEMNISE, self::DOSSIER_KO_REJETE,
        ]);
    }

    public function estAccepte(): bool
    {
        return str_starts_with($this->value, 'OK');
    }

    public function estRejete(): bool
    {
        return str_starts_with($this->value, 'KO');
    }

    public static function fromSlug($slug): ?self
    {
        foreach (self::cases() as $etat) {
            if ($etat->slugAction() === $slug) {
                return $etat;
            }
        }

        return null;
    }
}
