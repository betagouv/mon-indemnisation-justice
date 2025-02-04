<?php

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
#[Route('/agent/redacteur')]
class RedacteurController extends AbstractController
{
    public function __construct(
        protected readonly BrisPorteRepository $brisPorteRepository,
        protected readonly AgentRepository $agentRepository,
    ) {
    }

    #[Route('/', name: 'app_agent_redacteur_accueil')]
    public function index(): Response
    {
        return $this->redirectToRoute('agent_redacteur_dossiers');
    }

    /**
     * @param Agent[] ...$agents
     */
    protected function normalizeRedacteur(...$agents): array
    {
        return array_map(
            fn (Agent $agent) => [
                'id' => $agent->getId(),
                'nom' => $agent->getNomComplet(true),
            ], ...$agents);
    }

    #[Route('/dossiers', name: 'agent_redacteur_dossiers')]
    public function nouveauxDossiers(): Response
    {
        return $this->render('agent/redacteur/recherche_dossiers.html.twig', [
            'react' => [
                'redacteurs' => $this->normalizeRedacteur($this->agentRepository->getRedacteurs()),
                'etats_dossier' => array_map(
                    fn (EtatDossierType $etat) => [
                        'id' => $etat->value,
                        'slug' => $etat->slugAction(),
                        'libelle' => $etat->libelleAction(),
                    ],
                    [EtatDossierType::DOSSIER_DEPOSE, EtatDossierType::DOSSIER_ACCEPTE, EtatDossierType::DOSSIER_REJETE]
                ),
            ],
            'dossiers' => $this->brisPorteRepository->getDossiersConstitues(),
        ]);
    }
}
