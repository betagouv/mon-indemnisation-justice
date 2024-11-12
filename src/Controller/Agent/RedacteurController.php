<?php

namespace App\Controller\Agent;

use App\Entity\Agent;
use App\Repository\BrisPorteRepository;
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
        dump($this->brisPorteRepository->decompteParEtat());

        return $this->render('agent/redacteur/index.html.twig', [
            'decompte' => $this->brisPorteRepository->decompteParEtat(),
            'dossiers' => $this->brisPorteRepository->getDossiersConstitues(),
        ]);
    }
}
