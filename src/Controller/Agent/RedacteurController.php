<?php

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
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
    ) {
    }

    #[Route('/', name: 'app_agent_redacteur_accueil')]
    public function index(): Response
    {
        return $this->redirectToRoute('agent_redacteur_dossiers');
    }

    #[Route('/dossiers', name: 'agent_redacteur_dossiers')]
    public function nouveauxDossiers(): Response
    {
        return $this->render('agent/redacteur/les_dossiers.html.twig', [
            'dossiers' => $this->brisPorteRepository->getDossiersConstitues(),
        ]);
    }
}
