<?php

namespace MonIndemnisationJustice\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Event\DossierDecideEvent;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Service\ImprimanteCourrier;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment;

#[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
#[Route('/agent/redacteur')]
class DossierController extends AgentController
{
    public function __construct(
        protected readonly BrisPorteRepository $dossierRepository,
        protected readonly AgentRepository $agentRepository,
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
        protected readonly ImprimanteCourrier $imprimanteCourrier,
        // A supprimer
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
        protected readonly Mailer $mailer,
        protected readonly Environment $twig,
        protected readonly DocumentManager $documentManager,
    ) {}

    #[Route('/', name: 'app_agent_redacteur_accueil')]
    public function index(): Response
    {
        return $this->redirectToRoute('agent_redacteur_dossiers');
    }

    #[Route('/dossiers', name: 'agent_redacteur_dossiers')]
    public function dossiers(NormalizerInterface $normalizer): Response
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
                'redacteurs' => $normalizer->normalize($this->agentRepository->getRedacteurs(), 'json', ['groups' => 'agent:resume']),
            ],
        ]);
    }

    #[IsGranted(Agent::ROLE_AGENT_LIAISON_BUDGET)]
    #[Route('/dossiers-a-transmettre', name: 'agent_redacteur_dossiers_a_transmettre')]
    public function dossiersATransmettre(NormalizerInterface $normalizer): Response
    {
        return $this->render('agent/dossier/dossiers_a_transmettre.html.twig');
    }

    #[IsGranted(Agent::ROLE_AGENT_LIAISON_BUDGET)]
    #[Route('/dossiers-en-attente-indemnisation', name: 'agent_redacteur_dossiers_en_attente_indemnisation')]
    public function dossiersEnAttenteIndemnisation(NormalizerInterface $normalizer): Response
    {
        return $this->render('agent/dossier/dossiers_en_attente_indemnisation.html.twig');
    }

    #[Route('/dossier/{id}', name: 'agent_redacteur_consulter_dossier')]
    public function consulterDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, NormalizerInterface $normalizer): Response
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
                        $this->getAgent()->hasRole(Agent::ROLE_AGENT_LIAISON_BUDGET) ? ['LIAISON_BUDGET'] : [],
                        $this->getAgent()->hasRole(Agent::ROLE_AGENT_BETAGOUV) ? ['BETAGOUV'] : [],
                    ),
                ],
                'dossier' => $normalizer->normalize($dossier, 'json', ['groups' => 'agent:detail']),
                'redacteurs' => $normalizer->normalize($this->agentRepository->getRedacteurs(), 'json', ['groups' => 'agent:resume']),
            ],
        ]);
    }

    #[IsGranted(Agent::ROLE_AGENT_ATTRIBUTEUR)]
    #[Route('/dossier/{id}/attribuer.json', name: 'agent_redacteur_attribuer_dossier', methods: ['POST'])]
    public function attribuerDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        if (!$dossier->estAAttribuer()) {
            throw new BadRequestException("Ce dossier n'est pas à attribuer");
        }
        $agent = $this->agentRepository->find($request->getPayload()->getInt('redacteur_id', 0));

        if (null === $agent || !$agent->hasRole(Agent::ROLE_AGENT_REDACTEUR)) {
            throw new BadRequestException("Cet agent n'est pas rédacteur");
        }

        $dossier
            ->setRedacteur($agent)
            ->changerStatut(EtatDossierType::DOSSIER_A_INSTRUIRE, agent: $this->getAgent(), contexte: [
                'redacteur' => $agent,
            ])
        ;
        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
        ], Response::HTTP_OK);
    }

    // TODO créer un voter https://symfony.com/doc/current/security/voters.html
    #[IsGranted(
        attribute: new Expression('is_granted("ROLE_AGENT_ATTRIBUTEUR") or is_granted("ROLE_AGENT_VALIDATEUR") or user.instruit(subject["dossier"])'),
        subject: [
            'dossier' => new Expression('args["dossier"]'),
        ]
    )]
    #[Route('/dossier/{id}/cloturer.json', name: 'agent_redacteur_marquer_doublon_papier_dossier', methods: ['POST'])]
    public function cloturer(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        $dossier->changerStatut(EtatDossierType::DOSSIER_CLOTURE, agent: $this->getAgent(), contexte: [
            'motif' => $request->getPayload()->get('motif'),
            'explication' => $request->getPayload()->get('explication'),
        ]);
        $this->dossierRepository->save($dossier);

        // Envoi du mail de confirmation.
        $this->mailer
            ->toRequerant($dossier->getRequerant())
            ->subject("Clôture du dossier {$dossier->getReference()}")
            ->htmlTemplate('email/cloture_dossier.html.twig', [
                'dossier' => $dossier,
            ])
            ->send()
        ;

        return new JsonResponse([
            'etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
        ], Response::HTTP_OK);
    }

    // TODO créer un voter https://symfony.com/doc/current/security/voters.html
    #[IsGranted(
        attribute: new Expression('is_granted("ROLE_AGENT_ATTRIBUTEUR") or is_granted("ROLE_AGENT_VALIDATEUR") or user.instruit(subject["dossier"])'),
        subject: [
            'dossier' => new Expression('args["dossier"]'),
        ]
    )]
    #[Route('/dossier/{id}/piece-jointe/ajouter.json', name: 'agent_redacteur_ajouter_piece_jointe_dossier', methods: ['POST'])]
    public function ajouterPieceJointe(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        $type = DocumentType::tryFrom($request->request->get('type'));
        if (null === $type) {
            return new JsonResponse([
                'error' => 'Type non reconnu ',
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        $content = $file->getContent();
        $filename = hash('sha256', $content).'.'.($file->guessExtension() ?? $file->getExtension());
        $this->storage->write($filename, $content);
        $document = ($type->estUnique() ? $dossier->getDocumentParType($type) ?? (new Document())->setType($type)->ajouterAuDossier($dossier) : (new Document())->setType($type)->ajouterAuDossier($dossier))
            ->setFilename($filename)
            ->setCorps(null)
            ->setOriginalFilename($file->getClientOriginalName())
            ->setSize($file->getSize())
            ->setAjoutRequerant(false)
            ->setMime($file->getClientMimeType())
        ;

        if ($request->request->has('metaDonnees')) {
            $metaDonnees = json_decode($request->request->get('metaDonnees'), true);

            $document->setMetaDonnees($metaDonnees);
        }

        $this->em->persist($document);
        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse($this->normalizer->normalize($document, 'json', ['agent:detail']), Response::HTTP_OK);
    }

    #[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
    #[Route('/dossier/{id}/demarrer-instruction.json', name: 'agent_redacteur_instruction_demarrer_dossier', methods: ['POST'])]
    public function demarrerInstruction(#[MapEntity(id: 'id')] BrisPorte $dossier): Response
    {
        if (EtatDossierType::DOSSIER_A_INSTRUIRE !== $dossier->getEtatDossier()->getEtat()) {
            return new JsonResponse(['error' => "Ce dossier n'est pas à instruire"], Response::HTTP_UNAUTHORIZED);
        }

        $agent = $this->getAgent();

        if ($agent !== $dossier->getRedacteur()) {
            return new JsonResponse(['error' => "Vous n'êtes pas attribué à l'instruction de ce dossier"], Response::HTTP_UNAUTHORIZED);
        }

        $dossier
            ->changerStatut(EtatDossierType::DOSSIER_EN_INSTRUCTION, agent: $agent)
        ;

        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
        ]);
    }

    #[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
    #[Route('/dossier/{id}/decider.json', name: 'agent_redacteur_decider_accepter_dossier', methods: ['POST'])]
    public function decider(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request, NormalizerInterface $normalizer): Response
    {
        $agent = $this->getAgent();

        if ($agent !== $dossier->getRedacteur()) {
            return new JsonResponse(['error' => "Vous n'êtes pas attribué à l'instruction de ce dossier"], Response::HTTP_UNAUTHORIZED);
        }

        $indemnisation = floatval($request->getPayload()->getBoolean('indemnisation'));
        $montantIndemnisation = floatval($request->getPayload()->get('montantIndemnisation'));
        $motif = $request->getPayload()->getString('motif');

        if ($indemnisation) {
            $dossier
                ->changerStatut(EtatDossierType::DOSSIER_OK_A_SIGNER, agent: $agent, contexte: $montantIndemnisation ? [
                    'montant' => $montantIndemnisation,
                ] : null)
                ->setPropositionIndemnisation($montantIndemnisation)
            ;
        } else {
            $dossier
                ->changerStatut(EtatDossierType::DOSSIER_KO_A_SIGNER, agent: $agent, contexte: $motif ? [
                    'motif' => $motif,
                ] : null)
            ;
        }

        $this->em->persist($dossier);

        $this->em->flush();

        return new JsonResponse([
            'etat' => $normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
            'document' => $this->normalizer->normalize($dossier->getDocumentParType(DocumentType::TYPE_COURRIER_MINISTERE), 'json', ['agent:detail']),
        ], Response::HTTP_OK);
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/dossier/{id}/courrier/generer.json', name: 'agent_redacteur_generer_courrier_dossier', methods: ['POST'])]
    public function genererCourrierDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        // TODO ajouter en métadonnées l'info du rejet ou de l'acceptation, ainsi que du montant de l'indemnisation
        $indemnisation = $request->getPayload()->getBoolean('indemnisation');

        $courrierMinistere = $dossier->getOrCreatePropositionIndemnisation();

        if ($indemnisation) {
            $montantIndemnisation = floatval($request->getPayload()->getString('montantIndemnisation'));
            $courrierMinistere->setMetaDonnees(['contexte' => [
                'indemnisation' => true,
                'montantIndemnisation' => $montantIndemnisation,
            ]]);

            if ($dossier->getRequerant()->getIsPersonneMorale()) {
                $courrierMinistere->setCorps(
                    $this->twig->render('courrier/_corps_accepte_personne_morale.html.twig', [
                        'dossier' => $dossier,
                        'montantIndemnisation' => $montantIndemnisation,
                    ])
                );
            } else {
                $courrierMinistere->setCorps(
                    $this->twig->render('courrier/_corps_accepte_personne_physique.html.twig', [
                        'dossier' => $dossier,
                        'montantIndemnisation' => $montantIndemnisation,
                    ])
                );
            }
        } else {
            $motifRefus = $request->getPayload()->get('motifRefus');

            $courrierMinistere->setMetaDonnees(['contexte' => [
                'indemnisation' => false,
                'motifRefus' => $motifRefus,
            ]]);

            if ('est_bailleur' === $motifRefus) {
                $courrierMinistere->setCorps(
                    $this->twig->render('courrier/_corps_rejete_bailleur.html.twig', [
                        'dossier' => $dossier,
                    ])
                );
            } elseif ('est_vise' === $motifRefus) {
                $courrierMinistere->setCorps(
                    $this->twig->render('courrier/_corps_rejete_est_vise.html.twig', [
                        'dossier' => $dossier,
                    ])
                );
            } elseif ('est_hebergeant' === $motifRefus) {
                $courrierMinistere->setCorps(
                    $this->twig->render('courrier/_corps_rejete_est_hebergeant.html.twig', [
                        'dossier' => $dossier,
                    ])
                );
            } else {
                $courrierMinistere->setCorps(
                    $this->twig->render('courrier/_corps_rejete.html.twig', [
                        'dossier' => $dossier,
                    ])
                );
            }
        }

        $courrierMinistere = $this->imprimanteCourrier->imprimerDocument($courrierMinistere);

        $this->em->persist($courrierMinistere);
        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse($this->normalizer->normalize($courrierMinistere, 'json', ['agent:detail']));
    }

    #[IsGranted(Agent::ROLE_AGENT_VALIDATEUR)]
    #[Route('/dossier/{id}/proposition-indemnisation/changer-montant.json', name: 'agent_redacteur_editer_courrier_dossier', methods: ['PUT'])]
    public function changerMontantIndemnisation(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        if (!$dossier->getEtatDossier()->estASigner()) {
            return new JsonResponse(['error' => "Cet dossier n'est pas à valider"], Response::HTTP_BAD_REQUEST);
        }

        $montantIndemnisation = floatval($request->getPayload()->get('montantIndemnisation'));
        $dossier->setPropositionIndemnisation($montantIndemnisation);

        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[IsGranted(
        attribute: new Expression('user.instruit(subject["dossier"])'),
        subject: [
            'dossier' => new Expression('args["dossier"]'),
        ]
    )]
    #[Route('/dossier/{id}/arrete-paiement/generer.json', name: 'agent_redacteur_generer_arrete_paiement_dossier', methods: ['POST'])]
    public function genererArretePaiementDossier(
        #[MapEntity(id: 'id')]
        BrisPorte $dossier,
        Request $request,
    ): Response {
        try {
            $this->documentManager->genererArretePaiement($dossier);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['document' => $this->normalizer->normalize($dossier->getArretePaiement(), 'json', ['groups' => ['agent:detail']])], Response::HTTP_CREATED);
    }

    #[IsGranted(
        attribute: new Expression('user.instruit(subject["dossier"])'),
        subject: [
            'dossier' => new Expression('args["dossier"]'),
        ]
    )]
    #[Route('/dossier/{id}/arrete-paiement/valider.json', name: 'agent_redacteur_valider_arrete_paiement_dossier', methods: ['POST'])]
    public function validerArretePaiementDossier(
        #[MapEntity(id: 'id')]
        BrisPorte $dossier,
    ): Response {
        if (null === $dossier->getDocumentParType(DocumentType::TYPE_ARRETE_PAIEMENT)) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $dossier->changerStatut(EtatDossierType::DOSSIER_OK_VERIFIE, agent: $this->getAgent());

        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse(['etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail'])]);
    }

    #[IsGranted(Agent::ROLE_AGENT_VALIDATEUR)]
    #[Route('/dossier/{id}/signer-courrier.json', name: 'agent_redacteur_signer_courrier_dossier', methods: ['POST'])]
    public function signerCourrierDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        if (!$dossier->getEtatDossier()->estASigner()) {
            return new JsonResponse(['error' => "Cet dossier n'est pas à valider"], Response::HTTP_BAD_REQUEST);
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('fichierSigne');

        $content = $file->getContent();
        $filename = hash('sha256', $content).'.'.($file->guessExtension() ?? $file->getExtension());
        $this->storage->write($filename, $content);

        $document = $dossier->getOrCreatePropositionIndemnisation()
            ->setFilename($filename)
            ->setOriginalFilename($file->getClientOriginalName())
            ->setSize($file->getSize())
            ->setMime($file->getClientMimeType())
        ;

        $this->em->persist($document);
        $this->em->flush();

        $dossier->ajouterDocument($document);
        $dossier->changerStatut($dossier->getEtatDossier()->estAccepte() ? EtatDossierType::DOSSIER_OK_A_APPROUVER : EtatDossierType::DOSSIER_KO_REJETE, agent: $this->getAgent());

        $this->dossierRepository->save($dossier);

        $eventDispatcher->dispatch(new DossierDecideEvent($dossier));

        return new JsonResponse([
            'document' => $this->normalizer->normalize($document, 'json', ['agent:detail']),
            'etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
        ], Response::HTTP_OK);
    }

    #[IsGranted(Agent::ROLE_AGENT_VALIDATEUR)]
    #[Route('/dossier/{id}/arrete-paiement/signer.json', name: 'agent_redacteur_signer_arrete_paiement', methods: ['POST'])]
    public function signerArretePaiement(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        if (EtatDossierType::DOSSIER_OK_VERIFIE !== $dossier->getEtatDossier()->getEtat()) {
            return new JsonResponse(['error' => "Cet dossier n'est pas à signer"], Response::HTTP_BAD_REQUEST);
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('fichierSigne');

        $content = $file->getContent();
        $filename = hash('sha256', $content).'.'.($file->guessExtension() ?? $file->getExtension());
        $this->storage->write($filename, $content);

        $document = $dossier->getOrCreateArretePaiement()
            ->setFilename($filename)
            ->setOriginalFilename($file->getClientOriginalName())
            ->setSize($file->getSize())
            ->setMime($file->getMimeType())
        ;

        $this->em->persist($document);
        $this->em->flush();

        $dossier->ajouterDocument($document);
        $dossier->changerStatut(EtatDossierType::DOSSIER_OK_A_INDEMNISER, agent: $this->getAgent());

        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
            'document' => $this->normalizer->normalize($document, 'json', ['agent:detail']),
        ], Response::HTTP_OK);
    }

    #[IsGranted(Agent::ROLE_AGENT_VALIDATEUR)]
    #[Route('/dossier/{id}/envoyer.json', name: 'agent_redacteur_envoyer_dossier', methods: ['POST'])]
    public function envoyer(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        $agent = $this->getAgent();

        if ($dossier->estAccepte()) {
            $dossier
                ->changerStatut(EtatDossierType::DOSSIER_OK_A_APPROUVER, agent: $agent)
            ;
        } else {
            $dossier
                ->changerStatut(EtatDossierType::DOSSIER_KO_REJETE, agent: $agent)
            ;
        }

        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
        ], Response::HTTP_OK);
    }

    #[Route('/dossiers.json', name: 'agent_redacteur_dossiers_json', methods: ['GET'])]
    public function dossiersJson(Request $request, NormalizerInterface $normalizer): Response
    {
        $taille = 20;
        $page = $request->query->getInt('p', 1);
        $paginator = $this->dossierRepository->rechercheDossiers(
            $page,
            $taille,
            $request->query->has('e')
                ? array_map(fn ($e) => EtatDossierType::fromSlug($e), self::extraireCritereRecherche($request, 'e'))
                : [
                    // EtatDossierType::DOSSIER_CLOTURE,
                    // EtatDossierType::DOSSIER_A_FINALISER,
                    EtatDossierType::DOSSIER_A_ATTRIBUER,
                    EtatDossierType::DOSSIER_A_INSTRUIRE,
                    EtatDossierType::DOSSIER_EN_INSTRUCTION,
                    EtatDossierType::DOSSIER_OK_A_SIGNER,
                    EtatDossierType::DOSSIER_OK_A_APPROUVER,
                    EtatDossierType::DOSSIER_OK_A_VERIFIER,
                    EtatDossierType::DOSSIER_OK_A_INDEMNISER,
                    EtatDossierType::DOSSIER_OK_INDEMNISE,
                    EtatDossierType::DOSSIER_KO_A_SIGNER,
                    EtatDossierType::DOSSIER_KO_REJETE,
                ],
            $this->agentRepository->findBy([
                'id' => array_filter(
                    self::extraireCritereRecherche($request, 'a'),
                    fn ($a) => is_numeric($a)
                ),
            ]),
            self::extraireCritereRecherche($request, 'r'),
            in_array('_', self::extraireCritereRecherche($request, 'a'))
        );

        return new JsonResponse(
            [
                'page' => $page,
                'taille' => $taille,
                'total' => $paginator->count(),
                'resultats' => $normalizer->normalize(
                    iterator_to_array(
                        $paginator->getIterator()
                    ),
                    'json',
                    ['groups' => 'agent:liste']
                ),
            ]
        );
    }

    #[Route('/dossier/{id}/annoter.json', name: 'agent_redacteur_annoter_dossier', methods: ['POST'])]
    public function annoterDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        $dossier->setNotes($request->getPayload()->get('notes'));
        $this->dossierRepository->save($dossier);

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }

    #[IsGranted(
        attribute: new Expression('is_granted("ROLE_AGENT_LIAISON_BUDGET") and subject["dossier"].getEtatDossier().estAEnvoyerPourIndemnisation()'),
        subject: [
            'dossier' => new Expression('args["dossier"]'),
        ]
    )]
    #[Route('/{id}/documents-a-transmettre', name: 'agent_redacteur_documents_a_transmettre_dossier', methods: ['GET'])]
    public function download(#[MapEntity] BrisPorte $dossier, Request $request): Response
    {
        $zip = new \ZipArchive();
        $zipName = tempnam(sys_get_temp_dir(), "dossier_{$dossier->getReference()}.zip");

        if (true !== $zip->open($zipName, \ZipArchive::CREATE)) {
            throw new \RuntimeException('Cannot open '.$zipName);
        }

        foreach ($dossier->getDocumentsATransmettre()->toArray() as $document) {
            $zip->addFromString($document->getOriginalFilename(), $this->storage->read($document->getFilename()));
        }

        return (new BinaryFileResponse($zipName, headers: [
            'Content-Type' => 'application/zip',
        ]))
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, preg_replace('/\//', '', "Dossier {$dossier->getReference()}.zip"))
        ;
    }

    #[IsGranted(
        attribute: new Expression('is_granted("ROLE_AGENT_LIAISON_BUDGET") and subject["dossier"].getEtatDossier().estAEnvoyerPourIndemnisation()'),
        subject: [
            'dossier' => new Expression('args["dossier"]'),
        ]
    )]
    #[Route('/dossier/{id}/envoyer-pour-indemnisation.json', name: 'agent_redacteur_envoyer_pour_indemnisation_dossier', methods: ['POST'])]
    public function envoyerPourIndemnisationDossier(#[MapEntity] BrisPorte $dossier): Response
    {
        $dossier->changerStatut(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT, agent: $this->getAgent());
        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
        ], Response::HTTP_OK);
    }

    #[IsGranted(
        attribute: new Expression('is_granted("ROLE_AGENT_LIAISON_BUDGET") and subject["dossier"].getEtatDossier().estEnAttenteIndemnisation()'),
        subject: [
            'dossier' => new Expression('args["dossier"]'),
        ]
    )]
    #[Route('/dossier/{id}/marquer-indemnise.json', name: 'agent_redacteur_marquer_indemnise_dossier', methods: ['POST'])]
    public function marquerIndemniseDossier(#[MapEntity] BrisPorte $dossier): Response
    {
        $dossier->changerStatut(EtatDossierType::DOSSIER_OK_INDEMNISE, agent: $this->getAgent());
        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
        ], Response::HTTP_OK);
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
