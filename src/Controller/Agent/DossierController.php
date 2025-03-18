<?php

namespace MonIndemnisationJustice\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\CourrierDossier;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use MonIndemnisationJustice\Service\ImprimanteCourrier;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
#[Route('/agent/redacteur')]
class DossierController extends AgentController
{
    private const ETATS_DOSSIERS_ELIGIBLES = [
        EtatDossierType::DOSSIER_A_INSTRUIRE,
        EtatDossierType::DOSSIER_OK_A_SIGNER,
        EtatDossierType::DOSSIER_OK_A_APPROUVER,
        EtatDossierType::DOSSIER_OK_A_INDEMNISER,
        EtatDossierType::DOSSIER_OK_INDEMNISE,
        EtatDossierType::DOSSIER_KO_A_SIGNER,
        EtatDossierType::DOSSIER_KO_REJETE,
    ];

    public function __construct(
        protected readonly BrisPorteRepository $dossierRepository,
        protected readonly AgentRepository $agentRepository,
        #[Target('default.storage')] protected readonly FilesystemOperator $storage,
        protected readonly ImprimanteCourrier $imprimanteCourrier,
        // A supprimer
        protected readonly EntityManagerInterface $em,
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
                    'dateNaissance' => $dossier->getRequerant()->getPersonnePhysique()->getDateNaissance() ? $dossier->getRequerant()->getPersonnePhysique()->getDateNaissance()->getTimestamp() * 1000 : null,
                    'communeNaissance' => $dossier->getRequerant()->getPersonnePhysique()->getCommuneNaissance(),
                    'paysNaissance' => $dossier->getRequerant()->getPersonnePhysique()->getPaysNaissance()?->getNom(),
                    'raisonSociale' => $dossier->getRequerant()->getIsPersonneMorale() ? $dossier->getRequerant()->getPersonneMorale()->getRaisonSociale() : null,
                    'siren' => $dossier->getRequerant()->getIsPersonneMorale() ? $dossier->getRequerant()->getPersonneMorale()->getSirenSiret() : null,
                ],
                'notes' => $dossier->getNotes(),
                'testEligibilite' => $dossier->getTestEligibilite() ? [
                    'estVise' => $dossier->getTestEligibilite()->estVise,
                    'estHebergeant' => $dossier->getTestEligibilite()->estHebergeant,
                    'estProprietaire' => $dossier->getTestEligibilite()->estProprietaire,
                    'aContacteAssurance' => $dossier->getTestEligibilite()->aContacteAssurance,
                    'aContacteBailleur' => $dossier->getTestEligibilite()->aContacteBailleur,
                    'description' => $dossier->getTestEligibilite()->description,
                ] : null,
                'adresse' => [
                    'ligne1' => $dossier->getAdresse()->getLigne1(),
                    'ligne2' => $dossier->getAdresse()->getLigne2(),
                    'codePostal' => $dossier->getAdresse()->getCodePostal(),
                    'localite' => $dossier->getAdresse()->getLocalite(),
                ],
                'dateOperation' => ($dossier->getDateOperationPJ()?->getTimestamp() * 1000) ?? null,
                'estPorteBlindee' => $dossier->getIsPorteBlindee(),
                'montantIndemnisation' => floatval($dossier->getPropositionIndemnisation()),
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
                                    'type' => $document->getType(),
                                ],
                                $documents
                            ),
                        ],
                        array_keys($dossier->getDocuments()),
                        $dossier->getDocuments()
                    ),
                ),
                'corpsCourrier' => $dossier->getCorpsCourrier(),
                'courrier' => $dossier->getCourrier() ? [
                    'id' => $dossier->getCourrier()->getId(),
                    'filename' => $dossier->getCourrier()->getFilename(),
                    'url' => $this->generateUrl('agent_redacteur_courrier_dossier', ['id' => $dossier->getId(), 'hash' => md5($dossier->getCourrier()->getFilename())]),
                ] : null,
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
                'agent' => [
                    'id' => $this->getAgent()->getId(),
                    'permissions' => array_merge(
                        $this->getAgent()->hasRole(Agent::ROLE_AGENT_ATTRIBUTEUR) ? ['ATTRIBUTEUR'] : [],
                        $this->getAgent()->hasRole(Agent::ROLE_AGENT_REDACTEUR) ? ['REDACTEUR'] : [],
                        $this->getAgent()->hasRole(Agent::ROLE_AGENT_VALIDATEUR) ? ['VALIDATEUR'] : [],
                    ),
                ],
                'redacteurs' => $this->normalizeRedacteur($this->agentRepository->getRedacteurs()),
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
                        $this->getAgent()->hasRole(Agent::ROLE_AGENT_REDACTEUR) ? ['REDACTEUR'] : [],
                        $this->getAgent()->hasRole(Agent::ROLE_AGENT_VALIDATEUR) ? ['VALIDATEUR'] : [],
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

    #[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
    #[Route('/dossier/{id}/decider.json', name: 'agent_redacteur_decider_accepter_dossier', methods: ['POST'])]
    public function deciderAccepterDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        $agent = $this->getAgent();

        if ($agent !== $dossier->getRedacteur()) {
            return new JsonResponse(['error' => "Vous n'êtes pas attribué à l'instruction de ce dossier"], Response::HTTP_UNAUTHORIZED);
        }

        $indemnisation = floatval($request->getPayload()->getBoolean('indemnisation'));
        $montantIndemnisation = floatval($request->getPayload()->get('montantIndemnisation'));
        $corpsCourrier = $request->getPayload()->get('corpsCourrier');
        $motif = $request->getPayload()->getString('motif');

        if ($indemnisation) {
            $dossier
            ->changerStatut(EtatDossierType::DOSSIER_OK_A_SIGNER, agent: $agent, contexte: $montantIndemnisation ? [
                'montant' => $montantIndemnisation,
            ] : null)
            ->setPropositionIndemnisation($montantIndemnisation)
            ->setCorpsCourrier($corpsCourrier);
        } else {
            $dossier
            ->changerStatut(EtatDossierType::DOSSIER_KO_A_SIGNER, agent: $agent, contexte: $motif ? [
                'motif' => $motif,
            ] : null)
            ->setCorpsCourrier($corpsCourrier);
        }

        $destination = $this->imprimanteCourrier->imprimerCourrier($dossier);
        $dossier->setCourrier(
            (new CourrierDossier())
                ->setDossier($dossier)
                ->setAgent($agent)
                ->setDateCreation(new \DateTimeImmutable())
                ->setFilename($destination)
        );

        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $dossier->getEtatDossier()->getEtat()->value,
            'courrier' => $dossier->getCourrier() ? [
                'id' => $dossier->getCourrier()->getId(),
                'filename' => $dossier->getCourrier()->getFilename(),
                'url' => $this->generateUrl('agent_redacteur_courrier_dossier', ['id' => $dossier->getId(), 'hash' => md5($dossier->getCourrier()->getFilename())]),
            ] : [],
        ], Response::HTTP_OK);
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/dossier/{id}/courrier/generer.html', name: 'agent_redacteur_generer_courrier_dossier', methods: ['POST'])]
    public function genererCourrierDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        $indemnisation = $request->getPayload()->getBoolean('indemnisation');

        if ($indemnisation) {
            return $this->render('courrier/_corps_accepte.html.twig', [
                'dossier' => $dossier,
                'montantIndemnisation' => floatval($request->getPayload()->getString('montantIndemnisation')),
            ]);
        } else {
            $motifRefus = $request->getPayload()->get('motifRefus');

            if ('est_vise' === $motifRefus) {
                return $this->render('courrier/_corps_rejete_est_vise.html.twig', [
                    'dossier' => $dossier,
                ]);
            }

            if ('est_hebergeant' === $motifRefus) {
                return $this->render('courrier/_corps_rejete_est_hebergeant.html.twig', [
                    'dossier' => $dossier,
                ]);
            }

            return $this->render('courrier/_corps_rejete.html.twig', [
                'dossier' => $dossier,
            ]);
        }
    }

    #[IsGranted(Agent::ROLE_AGENT_VALIDATEUR)]
    #[Route('/dossier/{id}/courrier/editer.json', name: 'agent_redacteur_editer_courrier_dossier', methods: ['POST'])]
    public function editerCourrierDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        if (!$dossier->getEtatDossier()->estASigner()) {
            return new JsonResponse(['error' => "Cet dossier n'est pas à valider"], Response::HTTP_BAD_REQUEST);
        }

        $montantIndemnisation = floatval($request->getPayload()->get('montantIndemnisation'));
        $corpsCourrier = $request->getPayload()->get('corpsCourrier');

        $dossier->setPropositionIndemnisation($montantIndemnisation)
            ->setCorpsCourrier($corpsCourrier);
        $destination = $this->imprimanteCourrier->imprimerCourrier($dossier);
        $dossier->setCourrier(
            (new CourrierDossier())
                ->setDossier($dossier)
                ->setAgent($this->getAgent())
                ->setDateCreation(new \DateTimeImmutable())
                ->setFilename($destination)
        );

        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $dossier->getEtatDossier()->getEtat()->value,
            'courrier' => $dossier->getCourrier() ? [
                'id' => $dossier->getCourrier()->getId(),
                'filename' => $dossier->getCourrier()->getFilename(),
                'url' => $this->generateUrl('agent_redacteur_courrier_dossier', ['id' => $dossier->getId(), 'hash' => md5($dossier->getCourrier()->getFilename())]),
            ] : [],
        ], Response::HTTP_OK);
    }

    #[IsGranted(Agent::ROLE_AGENT_VALIDATEUR)]
    #[Route('/dossier/{id}/courrier/signer.json', name: 'agent_redacteur_signer_courrier_dossier', methods: ['POST'])]
    public function signerCourrierDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        if (!$dossier->getEtatDossier()->estASigner()) {
            return new JsonResponse(['error' => "Cet dossier n'est pas à valider"], Response::HTTP_BAD_REQUEST);
        }

        $agent = $this->getAgent();

        /** @var UploadedFile $file */
        $file = $request->files->get('fichierSigne');

        $content = $file->getContent();
        $filename = hash('sha256', $content).'.'.($file->guessExtension() ?? $file->getExtension());
        $this->storage->write($filename, $content);
        $document = (new Document())
                ->setFilename($filename)
                ->setOriginalFilename($file->getClientOriginalName())
                ->setSize($file->getSize())
                ->setType(Document::TYPE_COURRIER_MINISTERE)
                ->setMime($file->getMimeType());

        $this->em->persist($document);
        $this->em->flush();

        $dossier->ajouterDocument($document);

        if ($dossier->estAccepte()) {
            $dossier
            ->changerStatut(EtatDossierType::DOSSIER_OK_A_APPROUVER, agent: $agent);
        } else {
            $dossier
            ->changerStatut(EtatDossierType::DOSSIER_KO_REJETE, agent: $agent);
        }

        $this->dossierRepository->save($dossier);

        // TODO faire partir le courriel notifiant le requérant

        return new JsonResponse([
            'etat' => $dossier->getEtatDossier()->getEtat()->value,
            'documents' => [
                Document::TYPE_COURRIER_MINISTERE => [
                    [
                        'id' => $document->getId(),
                        'mime' => $document->getMime(),
                        'originalFilename' => $document->getOriginalFilename(),
                        'url' => $this->generateUrl('agent_document_download', ['id' => $document->getId(), 'hash' => md5($document->getFilename())]),
                        'type' => $document->getType(),
                    ],
                ],
            ],
        ], Response::HTTP_OK);
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

    #[Route('/dossier/{id}/annoter.json', name: 'agent_redacteur_annoter_dossier', methods: ['POST'])]
    public function annoterDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        $dossier->setNotes($request->getPayload()->get('notes'));
        $this->dossierRepository->save($dossier);

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
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
