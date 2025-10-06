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
}
