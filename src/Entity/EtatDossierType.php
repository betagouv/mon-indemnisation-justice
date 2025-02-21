<?php

namespace MonIndemnisationJustice\Entity;

enum EtatDossierType: string
{
    // Le requérant a initié un dossier qu'il n'a pas encore déposé
    case DOSSIER_A_FINALISER = 'A_FINALISER';
    // Le requérant a finalisé et déposé son dossier
    case DOSSIER_A_INSTRUIRE = 'A_INSTRUIRE';
    case DOSSIER_OK_A_VALIDER = 'OK_A_VALIDER';
    case DOSSIER_OK_A_SIGNER = 'OK_A_SIGNER';

    case DOSSIER_OK_A_INDEMNISER = 'OK_A_INDEMNISER';

    case DOSSIER_OK_REFUSE = 'OK_REFUSE';
    case DOSSIER_KO_A_VALIDER = 'KO_A_VALIDER';
    case DOSSIER_KO_A_SIGNER = 'KO_A_SIGNER';

    case DOSSIER_REJETE = 'REJETE';

    case DOSSIER_ACCEPTE = 'ACCEPTE';

    public function slugAction(): string
    {
        return preg_replace('/_/', '-', strtolower($this->value));
    }

    public function getLibelle(): string
    {
        return match ($this) {
            self::DOSSIER_A_FINALISER => "Demande d'indemnisation en cours de constitution",
            self::DOSSIER_A_INSTRUIRE => "Demande d'indemnisation déposée",
            self::DOSSIER_OK_A_VALIDER => "Demande d'indemnisation validée (en attente validation)",
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
