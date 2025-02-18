<?php

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
#[Route('/agent/redacteur')]
class DossierController extends AgentController
{
    private const ETATS_DOSSIERS_ELIGIBLES = [EtatDossierType::DOSSIER_DEPOSE, EtatDossierType::DOSSIER_ACCEPTE, EtatDossierType::DOSSIER_REJETE];

    public function __construct(
        protected readonly BrisPorteRepository $dossierRepository,
        protected readonly AgentRepository $agentRepository,
    ) {
    }

    #[Route('/', name: 'app_agent_redacteur_accueil')]
    public function index(): Response
    {
        return $this->redirectToRoute('agent_redacteur_dossiers');
    }

    protected function normalizeDossier(BrisPorte $dossier, $format = 'apercu'): array
    {
        return array_merge(
            [
                'id' => $dossier->getId(),
                'reference' => $dossier->getReference(),
                'etat' => $dossier->getEtatDossier()->getEtat()->value,
                'dateDepot' => $dossier->getDateDeclaration() ? (int) $dossier->getDateDeclaration()->format('Uv') : null,
                'attributaire' => $dossier->getRedacteur()?->getId(),
            ],
            'apercu' === $format ? [
                'requerant' => $dossier->getRequerant()->getNomCourant(capital: true),
                'adresse' => $dossier->getAdresse()->getLibelle(),
            ] : [
                'redacteur' => $dossier->getRedacteur()?->getId(),
                'requerant' => [
                    'civilite' => $dossier->getRequerant()->getPersonnePhysique()->getCivilite()->value,
                    'nom' => $dossier->getRequerant()->getPersonnePhysique()->getNom(),
                    'prenoms' => [
                        $dossier->getRequerant()->getPersonnePhysique()->getPrenom1(),
                        $dossier->getRequerant()->getPersonnePhysique()->getPrenom2(),
                        $dossier->getRequerant()->getPersonnePhysique()->getPrenom3(),
                    ],
                    'nomNaissance' => $dossier->getRequerant()->getPersonnePhysique()->getNomNaissance(),
                    'dateNaissance' => $dossier->getRequerant()->getPersonnePhysique()->getDateNaissance() ? (int) $dossier->getRequerant()->getPersonnePhysique()->getDateNaissance()->format('Uv') : null,
                    'communeNaissance' => $dossier->getRequerant()->getPersonnePhysique()->getCommuneNaissance(),
                    'paysNaissance' => $dossier->getRequerant()->getPersonnePhysique()->getPaysNaissance()?->getNom(),
                    'raisonSociale' => $dossier->getRequerant()->getIsPersonneMorale() ? $dossier->getRequerant()->getPersonneMorale()->getRaisonSociale() : null,
                    'siren' => $dossier->getRequerant()->getIsPersonneMorale() ? $dossier->getRequerant()->getPersonneMorale()->getSirenSiret() : null,
                ],
                'adresse' => [
                    'ligne1' => $dossier->getAdresse()->getLigne1(),
                    'ligne2' => $dossier->getAdresse()->getLigne2(),
                    'codePostal' => $dossier->getAdresse()->getCodePostal(),
                    'localite' => $dossier->getAdresse()->getLocalite(),
                ],
                'dateOperation' => ($dossier->getDateOperationPJ()?->getTimestamp() * 1000) ?? null,
                'estPorteBlindee' => $dossier->getIsPorteBlindee(),
                'documents' => array_merge(
                    /* @var Document[] $documents */
                    ...array_map(
                        fn (string $type, array $documents) => [
                            $type => array_map(
                                fn (Document $document) => [
                                    'id' => $document->getId(),
                                    'mime' => $document->getMime(),
                                    'originalFilename' => $document->getOriginalFilename(),
                                    'url' => $this->generateUrl('agent_document_download', ['id' => $document->getId(), 'hash' => md5($document->getFilename())]),
                                ],
                                $documents
                            ),
                        ],
                        array_keys($dossier->getDocuments()),
                        $dossier->getDocuments()
                    ),
                ),
            ]
        );
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
        return $this->render('agent/dossier/recherche_dossiers.html.twig', [
            'react' => [
                'redacteurs' => $this->normalizeRedacteur($this->agentRepository->getRedacteurs()),
                'etats_dossier' => array_map(
                    fn (EtatDossierType $etat) => [
                        'id' => $etat->value,
                        'slug' => $etat->slugAction(),
                        'libelle' => $etat->libelleAction(),
                    ],
                    self::ETATS_DOSSIERS_ELIGIBLES
                ),
            ],
        ]);
    }

    #[Route('/dossier/{id}', name: 'agent_redacteur_consulter_dossier')]
    public function consulterDossier(#[MapEntity(id: 'id')] BrisPorte $dossier): Response
    {
        return $this->render('agent/dossier/consulter_bris_porte.html.twig', [
            'titre' => 'Traitement du bris de porte '.$dossier->getReference(),
            'react' => [
                'agent' => [
                    'id' => $this->getAgent()->getId(),
                    'permissions' => array_merge(
                        $this->getAgent()->hasRole(Agent::ROLE_AGENT_ATTRIBUTEUR) ? ['ATTRIBUTEUR'] : [],
                    ),
                ],
                'dossier' => $this->normalizeDossier($dossier, 'detail'),
                'redacteurs' => $this->normalizeRedacteur($this->agentRepository->getRedacteurs()),
            ],
        ]);
    }

    #[IsGranted(Agent::ROLE_AGENT_ATTRIBUTEUR)]
    #[Route('/dossier/{id}/attribuer.json', name: 'agent_redacteur_attribuer_dossier', methods: ['POST'])]
    public function attribuerDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        $agent = $this->agentRepository->find($request->getPayload()->getInt('redacteur_id', 0));

        if (null === $agent || !$agent->hasRole(Agent::ROLE_AGENT_REDACTEUR)) {
            return new JsonResponse(['error' => "Cet agent n'est pas rédacteur"], Response::HTTP_BAD_REQUEST);
        }

        $dossier->setRedacteur($agent);
        $this->dossierRepository->save($dossier);

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }

    #[Route('/dossiers.json', name: 'agent_redacteur_dossiers_json', methods: ['GET'])]
    public function dossiersJson(Request $request): Response
    {
        return new JsonResponse(
            array_map(
                fn (BrisPorte $dossier) => $this->normalizeDossier($dossier),
                $this->dossierRepository->rechercheDossiers(
                    $request->query->has('e') ?
                        array_filter(
                            array_map(fn ($e) => EtatDossierType::fromSlug($e), self::extraireCritereRecherche($request, 'e')),
                            fn (EtatDossierType $etat) => in_array($etat, self::ETATS_DOSSIERS_ELIGIBLES)
                        ) :
                        self::ETATS_DOSSIERS_ELIGIBLES,
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
