<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\BrisPorteRepository;

/**
 * Service qui assure les opérations essentielles autour du dossier, notamment la gestion de l'état.
 */
class DossierManager
{
    public function __construct(
        protected readonly BrisPorteRepository $dossierRepository,
        protected readonly AgentRepository $agentRepository,
    ) {}

    /**
     * On parle de revenir à un n-ème état précédent, mais en réalité on va juste re-créer un nouvel état équivalent en
     * dessus de la pile d'historique d'état, lequel devient alors l'état courant.
     */
    public function revenir(BrisPorte $dossier, int $nbEtapes): void
    {
        $dossier->revenir($nbEtapes);
        $this->dossierRepository->save($dossier);
    }

    public function avancer(BrisPorte $dossier, ?Agent $agent = null, ?array $contexte = null): void
    {
        /** @var EtatDossierType $etat */
        $etat = $dossier->getEtatDossier()->getEtat()->etatSuivant($contexte ?? []);

        $dossier->changerStatut(
            $etat,
            requerant: in_array($etat, [EtatDossierType::DOSSIER_A_ATTRIBUER, EtatDossierType::DOSSIER_OK_A_VERIFIER]),
            agent: $agent,
            contexte: $contexte
        );

        $this->dossierRepository->save($dossier);
    }

    /**
     * On annule l'état courant en le supprimant de l'historique, l'état actuel repointant sur l'état précédant.
     */
    public function annuler(BrisPorte $dossier): void
    {
        $dossier->annulerEtat();
        $this->dossierRepository->save($dossier);
    }
}
