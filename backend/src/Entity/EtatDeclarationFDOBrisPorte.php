<?php

namespace MonIndemnisationJustice\Entity;

enum EtatDeclarationFDOBrisPorte: string
{
    case BROUILLON = 'BROUILLON';
    case ENVOYEE = 'ENVOYEE';
    case DEMANDE_INITIEE = 'DEMANDE_INITIEE';
    case DEMANDE_DEPOSEE = 'DEMANDE_DEPOSEE';
    case DEMANDE_EN_INSTRUCTION = 'DEMANDE_EN_INSTRUCTION';
    case DEMANDE_ACCEPTEE = 'DEMANDE_ACCEPTEE';
    case DEMANDE_REJETEE = 'DEMANDE_REJETEE';

    public static function depuisDeclaration(DeclarationFDOBrisPorte $declaration): self
    {
        if ($declaration->estAttribuee()) {
            $etatDossier = $declaration->getDossier()->getEtatDossier();

            return match ($etatDossier->getEtat()) {
                EtatDossierType::DOSSIER_A_FINALISER => self::DEMANDE_INITIEE,
                EtatDossierType::DOSSIER_A_ATTRIBUER, EtatDossierType::DOSSIER_EN_INSTRUCTION => self::DEMANDE_DEPOSEE,
                EtatDossierType::DOSSIER_CLOTURE, EtatDossierType::DOSSIER_KO_REJETE => self::DEMANDE_REJETEE,
                EtatDossierType::DOSSIER_OK_A_APPROUVER, EtatDossierType::DOSSIER_OK_A_VERIFIER, EtatDossierType::DOSSIER_OK_VERIFIE, EtatDossierType::DOSSIER_OK_A_INDEMNISER, EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT, EtatDossierType::DOSSIER_OK_INDEMNISE => self::DEMANDE_ACCEPTEE,
                default => self::DEMANDE_EN_INSTRUCTION,
            };
        }
        if ($declaration->estBrouillon()) {
            return self::BROUILLON;
        }

        return self::ENVOYEE;
    }

    public function getDate(DeclarationFDOBrisPorte $declarationFDOBrisPorte): \DateTimeImmutable
    {
        return match ($this) {
            self::BROUILLON => $declarationFDOBrisPorte->getDateCreation(),
            self::ENVOYEE => $declarationFDOBrisPorte->getDateSoumission(),
            self::DEMANDE_INITIEE => $declarationFDOBrisPorte->getDossier()->getDateCreation(),
            self::DEMANDE_DEPOSEE => $declarationFDOBrisPorte->getDossier()->getDateDepot(),
            self::DEMANDE_EN_INSTRUCTION => $declarationFDOBrisPorte->getDossier()->getEtat(EtatDossierType::DOSSIER_A_INSTRUIRE)?->getDate(),
            self::DEMANDE_ACCEPTEE => $declarationFDOBrisPorte->getDossier()->getEtat(EtatDossierType::DOSSIER_OK_A_APPROUVER)->getDate(),
            self::DEMANDE_REJETEE => $declarationFDOBrisPorte->getDossier()->estCloture() ?
                $declarationFDOBrisPorte->getDossier()->getEtat(EtatDossierType::DOSSIER_CLOTURE)->getDate() :
                $declarationFDOBrisPorte->getDossier()->getEtat(EtatDossierType::DOSSIER_KO_REJETE)->getDate(),
        };
    }
}
