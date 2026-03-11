<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\EtatDossierType;

enum EtatDossierUsager: string
{
    case A_COMPLETER = 'A_COMPLETER';
    case DEPOSE = 'DEPOSE';
    case EN_INSTRUCTION = 'EN_INSTRUCTION';
    case OK_A_ACCEPTER = 'OK_A_ACCEPTER';
    case OK_A_INDEMNISER = 'OK_A_INDEMNISER';
    case OK_INDEMNISE = 'OK_INDEMNISE';
    case KO_REJETE = 'KO_REJETE';
    case CLOTURE = 'CLOTURE';

    public static function depuisEtatDossier(EtatDossierType $etatDossier): EtatDossierUsager
    {
        return match ($etatDossier) {
            EtatDossierType::DOSSIER_A_FINALISER => self::A_COMPLETER,
            EtatDossierType::DOSSIER_A_ATTRIBUER, EtatDossierType::DOSSIER_A_INSTRUIRE => self::DEPOSE,
            EtatDossierType::DOSSIER_EN_INSTRUCTION, EtatDossierType::DOSSIER_OK_A_SIGNER, EtatDossierType::DOSSIER_KO_A_SIGNER => self::EN_INSTRUCTION,
            EtatDossierType::DOSSIER_OK_A_APPROUVER => self::OK_A_ACCEPTER,
            EtatDossierType::DOSSIER_OK_A_VERIFIER, EtatDossierType::DOSSIER_OK_VERIFIE, EtatDossierType::DOSSIER_OK_A_INDEMNISER, EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT => self::OK_A_INDEMNISER,
            EtatDossierType::DOSSIER_OK_INDEMNISE => self::OK_INDEMNISE,
            EtatDossierType::DOSSIER_KO_REJETE => self::KO_REJETE,
            EtatDossierType::DOSSIER_CLOTURE => self::CLOTURE,
        };
    }
}
