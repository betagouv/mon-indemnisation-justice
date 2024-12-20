<?php

namespace App\Controller\Agent;

use App\Entity\Agent;
use App\Entity\BrisPorte;
use App\Entity\EtatDossierType;
use App\Repository\BrisPorteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
#[Route('/agent/redacteur')]
class RedacteurController extends AbstractController
{
    public function __construct(
        protected readonly BrisPorteRepository $brisPorteRepository,
    ) {
    }

    #[Route('/', name: 'app_agent_redacteur_accueil')]
    public function index(): Response
    {
        /** @var $agent Agent */
        $agent = $this->getUser();
        if ($agent->hasRole(Agent::ROLE_AGENT_VALIDATEUR)) {
            return $this->redirectToRoute('agent_redacteur_dossiers');
        }

        return $this->redirectToRoute('agent_redacteur_nouveaux_dossiers');
    }

    #[Route('/dossiers/tous', name: 'agent_redacteur_dossiers')]
    public function nouveauxDossiers(): Response
    {
        return $this->render('agent/redacteur/tous_les_dossiers.html.twig', [
            'dossiers' => $this->brisPorteRepository->getDossiersConstitues(),
        ]);
    }

    #[Route('/dossier/{id}/consulter', name: 'agent_bris_porte_consulter', methods: ['GET'])]
    public function consulter(BrisPorte $dossier): Response
    {
        return $this->render('agent/redacteur/consulter_bris_porte.html.twig', [
            'dossier' => $dossier,
        ]);
    }
}
