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
        dump($this->brisPorteRepository->decompteParEtat());

        return $this->render('agent/redacteur/index.html.twig', [
            'decompte' => $this->brisPorteRepository->decompteParEtat(),
            'dossiers' => $this->brisPorteRepository->getDossiersConstitues(),
        ]);
    }

    #[Route('/dossier/{id}/consulter', name: 'agent_bris_porte_consulter', methods: ['GET'])]
    public function consulter(BrisPorte $dossier): Response
    {
        return $this->render('agent/bris_porte/consulter_bris_porte.html.twig', [
            'decompte' => $this->brisPorteRepository->decompteParEtat(),
            'dossier' => $dossier,
        ]);
    }

    #[Route('/dossier/{id}/pre-valider', name: 'agent_dossier_pre_valider', methods: ['POST'])]
    public function preValider(BrisPorte $dossier, Request $request): Response
    {
        if ($this->isCsrfTokenValid('preValiderDossier', $request->request->get('_token'))) {
            $dossier->changerStatut(EtatDossierType::DOSSIER_PRE_VALIDE, agent: $this->getUser());
            $this->brisPorteRepository->save($dossier);

            $this->addFlash('success', [
                'title' => "Le dossier {$dossier->getReference()} a bien été envoyé pour validation",
                'message' => "Le dossier est envoyé à la personne chargée de la validation des dossiers"
            ]);
        }

        return $this->redirectToRoute('app_agent_redacteur_accueil');
    }
}
