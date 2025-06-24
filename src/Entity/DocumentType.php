<?php

namespace MonIndemnisationJustice\Entity;

enum DocumentType: string
{
    case TYPE_ATTESTATION_INFORMATION = 'attestation_information';
    case TYPE_PHOTO_PREJUDICE = 'photo_prejudice';
    case TYPE_CARTE_IDENTITE = 'carte_identite';
    case TYPE_FACTURE = 'facture';
    // case TYPE_PREUVE_PAIEMENT_FACTURE = 'preuve_paiement_facture';
    case TYPE_RIB = 'rib';
    case TYPE_TITRE_PROPRIETE = 'titre_propriete';
    case TYPE_CONTRAT_LOCATION = 'contrat_location';
    case TYPE_ATTESTATION_NON_PRISE_EN_CHARGE_BAILLEUR = 'non_prise_en_charge_bailleur';
    case TYPE_ATTESTATION_NON_PRISE_EN_CHARGE_ASSURANCE = 'non_prise_en_charge_assurance';
    case TYPE_COURRIER_MINISTERE = 'courrier_ministere';
    // a.k.a. la déclaration d'acceptation
    case TYPE_COURRIER_REQUERANT = 'courrier_requerant';
    case TYPE_ARRETE_PAIEMENT = 'arrete_paiement';

    public function getLibelle(): string
    {
        return match ($this) {
            self::TYPE_ATTESTATION_INFORMATION => "Attestation à remettre en cas d'erreur de porte",
            self::TYPE_PHOTO_PREJUDICE => 'Photo de la porte endommagée',
            self::TYPE_CARTE_IDENTITE => "Pièce d'identité",
            self::TYPE_FACTURE => 'Facture',
            self::TYPE_RIB => 'RIB',
            self::TYPE_TITRE_PROPRIETE => 'Titre de propriété',
            self::TYPE_CONTRAT_LOCATION => 'Contrat de location',
            self::TYPE_ATTESTATION_NON_PRISE_EN_CHARGE_BAILLEUR => 'Attestation de non prise en charge par le bailleur',
            self::TYPE_ATTESTATION_NON_PRISE_EN_CHARGE_ASSURANCE => "Attestation de non prise en charge par l'assurance habitation",
            self::TYPE_COURRIER_MINISTERE => 'Courrier signé de décision du Ministère',
            self::TYPE_COURRIER_REQUERANT => "Courrier signé d'acceptation du requérant",
            self::TYPE_ARRETE_PAIEMENT => 'Arrêté de paiement',
        };
    }

    public function estEditableAgent(): bool
    {
        return match ($this) {
            self::TYPE_COURRIER_MINISTERE,
            self::TYPE_ARRETE_PAIEMENT => true,

            default => false,
        };
    }

    /**
     * Retourne le chemin vers le gabarit (i.e. "template") twig à utiliser pour la génération du document.
     */
    public function getGabarit(): ?string
    {
        return match ($this) {
            self::TYPE_COURRIER_MINISTERE => 'courrier/decision.html.twig',
            self::TYPE_ARRETE_PAIEMENT => 'courrier/arretePaiement.html.twig',

            default => null,
        };
    }
}
