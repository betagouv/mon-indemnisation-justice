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
            return $this->redirectToRoute('agent_redacteur_dossiers_a_valider');
        }

        return $this->redirectToRoute('agent_redacteur_nouveaux_dossiers');
    }

    #[Route('/dossiers/nouveaux', name: 'agent_redacteur_nouveaux_dossiers')]
    public function nouveauxDossiers(): Response
    {
        return $this->render('agent/redacteur/nouveaux_dossiers.html.twig', [
            'decompte' => $this->getDecompteDossiers(),
            'dossiers' => $this->brisPorteRepository->getDossiersConstitues(),
        ]);
    }

    #[Route('/dossiers/a-valider', name: 'agent_redacteur_dossiers_a_valider')]
    public function dossiersAValider(): Response
    {
        return $this->render('agent/redacteur/dossiers_a_valider.html.twig', [
            'decompte' => $this->getDecompteDossiers(),
            'dossiers' => $this->brisPorteRepository->getDossiersAValider(),
        ]);
    }

    #[Route('/dossiers/valides', name: 'agent_redacteur_dossiers_valides')]
    public function dossiersValides(): Response
    {
        return $this->render('agent/redacteur/dossiers_acceptes.html.twig', [
            'decompte' => $this->getDecompteDossiers(),
            'dossiers' => $this->brisPorteRepository->getDossiersAValider(),
        ]);
    }

    #[Route('/dossiers/refuses', name: 'agent_redacteur_dossiers_refuses')]
    public function dossiersRefuses(): Response
    {
        return $this->render('agent/redacteur/dossiers_refuses.html.twig', [
            'decompte' => $this->getDecompteDossiers(),
            'dossiers' => $this->brisPorteRepository->getDossiersAValider(),
        ]);
    }

    #[Route('/dossier/{id}/consulter', name: 'agent_bris_porte_consulter', methods: ['GET'])]
    public function consulter(BrisPorte $dossier): Response
    {
        return $this->render('agent/redacteur/consulter_bris_porte.html.twig', [
            'decompte' => $this->getDecompteDossiers(),
            'dossier' => $dossier,
            // Test
            'indemnisation' => 1234
        ]);
    }

    #[Route('/dossier/{id}/statuer/pre-validation', name: 'agent_dossier_statuer_pre_validation', methods: ['POST'])]
    public function statuerPreValidation(BrisPorte $dossier, Request $request): Response
    {
        return $this->render('agent/redacteur/consulter_bris_porte.html.twig', [
            'decompte' => $this->getDecompteDossiers(),
            'indemnisation' => (int) $request->request->get('indemnisation'),
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

    protected function getDecompteDossiers(): array
    {
        $decomptes = $this->brisPorteRepository->decompteParEtat();

        // TODO: maybe cache

        return [
            'nouveaux' => $decomptes[EtatDossierType::DOSSIER_DEPOSE->value] ?? 0,
            'aValider' => ($decomptes[EtatDossierType::DOSSIER_PRE_VALIDE->value] ?? 0) + ($decomptes[EtatDossierType::DOSSIER_PRE_REFUSE->value] ?? 0),
            'acceptes' => $decomptes[EtatDossierType::DOSSIER_ACCEPTE->value] ?? 0,
            'refuses' => $decomptes[EtatDossierType::DOSSIER_REFUSE->value] ?? 0
        ];
    }
}
