<?php

namespace App\Entity;

enum EtatDossierType: string
{
    case DOSSIER_INITIE = 'DOSSIER_INITIE';
    case DOSSIER_DEPOSE = 'DOSSIER_DEPOSE';
    case DOSSIER_PRE_VALIDE = 'DOSSIER_PRE_VALIDE';

    case DOSSIER_PRE_REFUSE = 'DOSSIER_PRE_REFUSE';
    case DOSSIER_ACCEPTE = 'DOSSIER_ACCEPTE';

    case DOSSIER_REFUSE = 'DOSSIER_REFUSE';

    /*
    case RENVOI_EN_CONSTITUTION = 'RENVOI_EN_CONSTITUTION';
    case VALIDE = 'VALIDE';
    case REJETE = 'REJETE';
    case SIGNATURE_VALIDEE = 'SIGNATURE_VALIDEE';
    case SIGNATURE_REJETEE = 'SIGNATURE_REJETEE';
    case ACCORD_OFFRE = 'ACCORD_OFFRE';
    case REFUS_OFFRE = 'REFUS_OFFRE';
     */

    public function getLibelle(): string
    {
        return match ($this) {
            self::DOSSIER_INITIE => "Demande d'indemnisation en cours de constitution",
            self::DOSSIER_DEPOSE => "Demande d'indemnisation déposée",
            self::DOSSIER_PRE_VALIDE => "Demande d'indemnisation validée (en attente signature)",
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


}
