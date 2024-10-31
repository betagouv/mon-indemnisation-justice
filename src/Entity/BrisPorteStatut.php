<?php

namespace App\Entity;

enum BrisPorteStatut: string
{
    case EN_COURS_DE_CONSTITUTION = 'EN_COURS_DE_CONSTITUTION';
    case CONSTITUE = 'CONSTITUE';
    case RENVOI_EN_CONSTITUTION = 'RENVOI_EN_CONSTITUTION';
    case VALIDE = 'VALIDE';
    case REJETE = 'REJETE';
    case SIGNATURE_VALIDEE = 'SIGNATURE_VALIDEE';
    case SIGNATURE_REJETEE = 'SIGNATURE_REJETEE';
    case ACCORD_OFFRE = 'ACCORD_OFFRE';
    case REFUS_OFFRE = 'REFUS_OFFRE';

    public function getLibelle(): string
    {
        return match ($this) {
            self::EN_COURS_DE_CONSTITUTION => "Demande d'indemnisation en cours de constitution",
            self::CONSTITUE => "Demande d'indemnisation constituée",
            self::RENVOI_EN_CONSTITUTION => "Demande de pièce(s) complémentaire(s) sur la demande d'indemnisation",
            self::VALIDE => "Demande d'indemnisation validée (en attente signature)",
            self::REJETE => "Demande d'indemnisation rejetée (en attente signature)",
            self::SIGNATURE_VALIDEE => "Demande d'indemnisation validée",
            self::SIGNATURE_REJETEE => "Demande d'indemnisation rejetée",
            self::ACCORD_OFFRE => "Proposition d'indemnisation acceptée",
            self::REFUS_OFFRE => "Proposition d'indemnisation rejetée",
        };
    }
}