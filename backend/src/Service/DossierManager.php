<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\DossierRepository;

/**
 * Service qui assure les opérations essentielles autour du dossier, notamment la gestion de l'état.
 */
class DossierManager
{
    public function __construct(
        protected readonly DossierRepository $dossierRepository,
        protected readonly AgentRepository $agentRepository,
    ) {
    }

    /**
     * On parle de revenir à un n-ème état précédent, mais en réalité on va juste re-créer un nouvel état équivalent en
     * dessus de la pile d'historique d'état, lequel devient alors l'état courant.
     */
    public function revenir(Dossier $dossier, int $nbEtapes): void
    {
        $dossier->revenir($nbEtapes);
        $this->dossierRepository->save($dossier);
    }

    public function changer(Dossier $dossier, EtatDossierType $etat, ?Agent $agent = null, ?array $contexte = null): Dossier
    {
        $dossier->changerStatut(
            $etat,
            requerant: in_array($etat, [EtatDossierType::DOSSIER_A_ATTRIBUER, EtatDossierType::DOSSIER_OK_A_VERIFIER]),
            agent: $agent,
            contexte: $contexte
        );

        $this->dossierRepository->save($dossier);

        return $dossier;
    }

    public function avancer(Dossier $dossier, ?Agent $agent = null, ?array $contexte = null): Dossier
    {
        $this->changer(
            $dossier,
            $dossier->getEtatDossier()->getEtat()->etatSuivant($contexte ?? []),
            agent: $agent,
            contexte: $contexte
        );


        return $dossier;
    }

    /**
     * On annule l'état courant en le supprimant de l'historique, l'état actuel repointant sur l'état précédant.
     */
    public function annuler(Dossier $dossier): void
    {
        $dossier->annulerEtat();
        $this->dossierRepository->save($dossier);
    }
}
