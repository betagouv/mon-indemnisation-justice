<?php

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function dossiers(): Response
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
        ]);
    }

    #[Route('/dossier/{id}', name: 'agent_redacteur_consulter_dossier')]
    public function consulterDossier(#[MapEntity(id: 'id')] BrisPorte $dossier): Response
    {
        return $this->render('agent/redacteur/consulter_bris_porte.html.twig', ['dossier' => $dossier]);
    }

    #[Route('/dossiers.json', name: 'agent_redacteur_dossiers_json', methods: ['GET'])]
    public function dossiersJson(Request $request): Response
    {
        return new JsonResponse(
            array_map(
                fn (BrisPorte $dossier) => [
                    'id' => $dossier->getId(),
                    'reference' => $dossier->getReference(),
                    'etat' => $dossier->getEtatDossier()->getEtat()->value,
                    'requerant' => $dossier->getRequerant()->getNomCourant(capital: true),
                    'adresse' => $dossier->getAdresse()->getLibelle(),
                    'dateDepot' => (int) $dossier->getDateDeclaration()?->format('Uv'),
                    'attributaire' => $dossier->getRedacteur()?->getId(),
                ],
                $this->brisPorteRepository->rechercheDossiers(
                    array_map(fn ($e) => EtatDossierType::fromSlug($e), self::extraireCritereRecherche($request, 'e')),
                    $this->agentRepository->findBy([
                        'id' => array_filter(
                            self::extraireCritereRecherche($request, 'a'),
                            fn ($a) => is_numeric($a)
                        ),
                    ]),
                    self::extraireCritereRecherche($request, 'r'),
                    in_array('_', self::extraireCritereRecherche($request, 'a'))
                )
            )
        );
    }

    private static function extraireCritereRecherche(Request $request, string $nom): array
    {
        if (!$request->query->has($nom)) {
            return [];
        }

        return array_filter(
            explode('|', $request->query->getString($nom, '')),
            fn ($v) => !empty($v)
        );
    }
}
