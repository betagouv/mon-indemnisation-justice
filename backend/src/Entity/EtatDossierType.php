<?php

namespace MonIndemnisationJustice\Entity;

use MonIndemnisationJustice\Event\Event\DossierArreteEditeEvent;
use MonIndemnisationJustice\Event\Event\DossierArreteSigneEvent;
use MonIndemnisationJustice\Event\Event\DossierAttribueEvent;
use MonIndemnisationJustice\Event\Event\DossierClotureEvent;
use MonIndemnisationJustice\Event\Event\DossierDeposeEvent;
use MonIndemnisationJustice\Event\Event\DossierEnCoursInstructionEvent;
use MonIndemnisationJustice\Event\Event\DossierIndemniseEvent;
use MonIndemnisationJustice\Event\Event\DossierInstruitPropositionEvent;
use MonIndemnisationJustice\Event\Event\DossierInstruitRejetEvent;
use MonIndemnisationJustice\Event\Event\DossierPropositionAccepteeEvent;
use MonIndemnisationJustice\Event\Event\DossierPropositionEnvoyeeEvent;
use MonIndemnisationJustice\Event\Event\DossierRejeteEvent;
use MonIndemnisationJustice\Event\Event\DossierTransitionEvent;
use MonIndemnisationJustice\Event\Event\DossierTransmisBudgetEvent;

enum EtatDossierType: string
{
    // Le requérant a initié un dossier qu'il n'a pas encore déposé
    case DOSSIER_A_FINALISER = 'A_FINALISER';
    // Le requérant a finalisé et déposé son dossier
    case DOSSIER_A_ATTRIBUER = 'A_ATTRIBUER';
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

    /**
     * Créer un évènement lorsque le dossier passe à cet état.
     */
    public function creerTransitionEvent(BrisPorte $dossier): ?DossierTransitionEvent
    {
        return match ($this) {
            self::DOSSIER_EN_INSTRUCTION => new DossierEnCoursInstructionEvent($dossier),
            self::DOSSIER_CLOTURE => new DossierClotureEvent($dossier),
            self::DOSSIER_A_ATTRIBUER => new DossierDeposeEvent($dossier),
            self::DOSSIER_A_INSTRUIRE => new DossierAttribueEvent($dossier),
            self::DOSSIER_OK_A_SIGNER => new DossierInstruitPropositionEvent($dossier),
            self::DOSSIER_OK_A_APPROUVER => new DossierPropositionEnvoyeeEvent($dossier),
            self::DOSSIER_OK_A_VERIFIER => new DossierPropositionAccepteeEvent($dossier),
            self::DOSSIER_OK_VERIFIE => new DossierArreteEditeEvent($dossier),
            self::DOSSIER_OK_A_INDEMNISER => new DossierArreteSigneEvent($dossier),
            self::DOSSIER_OK_EN_ATTENTE_PAIEMENT => new DossierTransmisBudgetEvent($dossier),
            self::DOSSIER_OK_INDEMNISE => new DossierIndemniseEvent($dossier),
            self::DOSSIER_KO_A_SIGNER => new DossierInstruitRejetEvent($dossier),
            self::DOSSIER_KO_REJETE => new DossierRejeteEvent($dossier),
            default => null,
        };
    }

    public function etatPrecedent(): ?EtatDossierType
    {
        return match ($this) {
            self::DOSSIER_CLOTURE, self::DOSSIER_EN_INSTRUCTION => self::DOSSIER_A_INSTRUIRE,
            self::DOSSIER_A_ATTRIBUER => self::DOSSIER_A_FINALISER,
            self::DOSSIER_A_INSTRUIRE => self::DOSSIER_A_ATTRIBUER,
            self::DOSSIER_OK_A_SIGNER, self::DOSSIER_KO_A_SIGNER => self::DOSSIER_EN_INSTRUCTION,
            self::DOSSIER_OK_A_APPROUVER => self::DOSSIER_OK_A_SIGNER,
            self::DOSSIER_OK_A_VERIFIER => self::DOSSIER_OK_A_APPROUVER,
            self::DOSSIER_OK_VERIFIE => self::DOSSIER_OK_A_VERIFIER,
            self::DOSSIER_OK_A_INDEMNISER => self::DOSSIER_OK_VERIFIE,
            self::DOSSIER_OK_EN_ATTENTE_PAIEMENT => self::DOSSIER_OK_A_INDEMNISER,
            self::DOSSIER_OK_INDEMNISE => self::DOSSIER_OK_EN_ATTENTE_PAIEMENT,
            self::DOSSIER_KO_REJETE => self::DOSSIER_KO_A_SIGNER,
            default => null,
        };
    }

    public function etatSuivant(array $contexte = []): ?EtatDossierType
    {
        return match ($this) {
            self::DOSSIER_A_FINALISER => self::DOSSIER_A_ATTRIBUER,
            self::DOSSIER_A_ATTRIBUER => self::DOSSIER_A_INSTRUIRE,
            self::DOSSIER_A_INSTRUIRE => self::DOSSIER_EN_INSTRUCTION,
            self::DOSSIER_EN_INSTRUCTION => isset($contexte['montantIndemnisation']) ? self::DOSSIER_OK_A_SIGNER : self::DOSSIER_KO_A_SIGNER,
            self::DOSSIER_OK_A_SIGNER => self::DOSSIER_OK_A_APPROUVER,
            self::DOSSIER_OK_A_APPROUVER => self::DOSSIER_OK_A_VERIFIER,
            self::DOSSIER_OK_A_VERIFIER => self::DOSSIER_OK_VERIFIE,
            self::DOSSIER_OK_VERIFIE => self::DOSSIER_OK_A_INDEMNISER,
            self::DOSSIER_OK_A_INDEMNISER => self::DOSSIER_OK_EN_ATTENTE_PAIEMENT,
            self::DOSSIER_OK_EN_ATTENTE_PAIEMENT => self::DOSSIER_OK_INDEMNISE,
            self::DOSSIER_KO_A_SIGNER => self::DOSSIER_KO_REJETE,
            default => null,
        };
    }

    public function estAAttribuer(): bool
    {
        return self::DOSSIER_A_ATTRIBUER === $this;
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
