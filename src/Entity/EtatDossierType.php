<?php

namespace MonIndemnisationJustice\Entity;

enum EtatDossierType: string
{
    case DOSSIER_INITIE = 'DOSSIER_INITIE';
    case DOSSIER_DEPOSE = 'DOSSIER_DEPOSE';
    case DOSSIER_ACCEPTE = 'DOSSIER_ACCEPTE';

    case DOSSIER_REJETE = 'DOSSIER_REJETE';

    public function slugAction(): string
    {
        return match ($this) {
            self::DOSSIER_INITIE => 'a-finaliser',
            self::DOSSIER_DEPOSE => 'a-instruire',
            self::DOSSIER_ACCEPTE => 'a-valider-acc',
            self::DOSSIER_REJETE => 'a-valider-rej',
        };
    }

    public function libelleAction(): string
    {
        return match ($this) {
            self::DOSSIER_INITIE => 'À finaliser',
            self::DOSSIER_DEPOSE => 'À instruire',
            self::DOSSIER_ACCEPTE => 'À valider (accepté)',
            self::DOSSIER_REJETE => 'À valider (rejeté)',
        };
    }

    public function getLibelle(): string
    {
        return match ($this) {
            self::DOSSIER_INITIE => "Demande d'indemnisation en cours de constitution",
            self::DOSSIER_DEPOSE => "Demande d'indemnisation déposée",
            self::DOSSIER_ACCEPTE => "Demande d'indemnisation validée (en attente signature)",
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
