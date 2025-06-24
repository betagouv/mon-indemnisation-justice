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
use MonIndemnisationJustice\Service\ImprimanteCourrier;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        #[Target('default.storage')] protected readonly FilesystemOperator $storage,
        protected readonly ImprimanteCourrier $imprimanteCourrier,
        // A supprimer
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
        protected readonly Mailer $mailer,
        protected readonly Environment $twig,
    ) {
    }

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
        $agent = $this->agentRepository->find($request->getPayload()->getInt('redacteur_id', 0));

        if (null === $agent || !$agent->hasRole(Agent::ROLE_AGENT_REDACTEUR)) {
            return new JsonResponse(['error' => "Cet agent n'est pas rédacteur"], Response::HTTP_BAD_REQUEST);
        }

        $dossier->setRedacteur($agent);
        $this->dossierRepository->save($dossier);

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
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
            ->send();

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
    public function ajouterPieceJointe(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
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
        $document = (new Document())
            ->setFilename($filename)
            ->setOriginalFilename($file->getClientOriginalName())
            ->setSize($file->getSize())
            ->setType($type)
            ->setMime($file->getMimeType());

        $dossier->ajouterDocument($document);

        $this->em->persist($document);

        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'id' => $document->getId(),
            'mime' => $document->getMime(),
            'originalFilename' => $document->getOriginalFilename(),
            'url' => $this->generateUrl('agent_document_download', ['id' => $document->getId(), 'hash' => md5($document->getFilename())]),
            'type' => $document->getType(),
        ], Response::HTTP_OK);
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
            ->changerStatut(EtatDossierType::DOSSIER_EN_INSTRUCTION, agent: $agent);

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
        $corpsCourrier = $request->getPayload()->get('corpsCourrier');
        $motif = $request->getPayload()->getString('motif');

        if ($indemnisation) {
            $dossier
                ->changerStatut(EtatDossierType::DOSSIER_OK_A_SIGNER, agent: $agent, contexte: $montantIndemnisation ? [
                    'montant' => $montantIndemnisation,
                ] : null)
                ->setPropositionIndemnisation($montantIndemnisation);
        } else {
            $dossier
                ->changerStatut(EtatDossierType::DOSSIER_KO_A_SIGNER, agent: $agent, contexte: $motif ? [
                    'motif' => $motif,
                ] : null);
        }

        $courrierDecision = $dossier->getDocumentParType(DocumentType::TYPE_COURRIER_MINISTERE);

        if (null === $courrierDecision) {
            $courrierDecision = (new Document())->setType(DocumentType::TYPE_COURRIER_MINISTERE);
            $dossier->ajouterDocument($courrierDecision);
        }

        $courrierDecision->setCorps($corpsCourrier);
        $courrierDecision = $this->imprimanteCourrier->imprimerLettreDecision($dossier, $courrierDecision)->setOriginalFilename("Lettre de décision - dossier {$dossier->getReference()}");

        $this->em->persist($courrierDecision);
        $this->em->persist($dossier);

        $this->em->flush();

        return new JsonResponse([
            'etat' => $normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
            'document' => $this->normalizer->normalize($courrierDecision, 'json', ['agent:detail']),
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

    #[IsGranted(
        attribute: new Expression('user.instruit(subject["dossier"])'),
        subject: [
            'dossier' => new Expression('args["dossier"]'),
        ]
    )]
    #[Route('/dossier/{id}/arrete-paiement/generer.json', name: 'agent_redacteur_generer_arrete_paiement_dossier', methods: ['POST'])]
    public function genererArretePaiementDossier(
        #[MapEntity(id: 'id')] BrisPorte $dossier,
        Request $request,
    ): Response {
        try {
            $arretePaiement = $dossier->getDocumentParType(DocumentType::TYPE_ARRETE_PAIEMENT);

            if (null === $arretePaiement) {
                $arretePaiement = (new Document())->setType(DocumentType::TYPE_ARRETE_PAIEMENT);
                $dossier->ajouterDocument($arretePaiement);

                $this->em->persist($dossier);
            }
            $arretePaiement->setCorps($request->getPayload()->get('corps'));
            $arretePaiement = $this->imprimanteCourrier->imprimerArretePaiement($dossier, $arretePaiement)
                ->setOriginalFilename("Arrêté de paiement - dossier {$dossier->getReference()}");

            $this->em->persist($arretePaiement);
            $this->em->flush();
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['document' => $this->normalizer->normalize($arretePaiement, 'json', ['groups' => ['agent:detail']])], Response::HTTP_CREATED);
    }

    #[IsGranted(
        attribute: new Expression('user.instruit(subject["dossier"])'),
        subject: [
            'dossier' => new Expression('args["dossier"]'),
        ]
    )]
    #[Route('/dossier/{id}/arrete-paiement/valider.json', name: 'agent_redacteur_valider_arrete_paiement_dossier', methods: ['POST'])]
    public function validerArretePaiementDossier(
        #[MapEntity(id: 'id')] BrisPorte $dossier,
        Request $request,
    ): Response {
        $document = $this->em->getRepository(Document::class)->find($request->getPayload()->getInt('document'));
        if (null === $document) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $dossier->ajouterDocument($document);
        $dossier->changerStatut(EtatDossierType::DOSSIER_OK_VERIFIE, agent: $this->getAgent());

        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse(['etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail'])]);
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
        $dossier->setPropositionIndemnisation($montantIndemnisation);

        $courrierDecision = $dossier->getDocumentParType(DocumentType::TYPE_COURRIER_MINISTERE);

        if (null === $courrierDecision) {
            $courrierDecision = (new Document())->setType(DocumentType::TYPE_COURRIER_MINISTERE);
            $dossier->ajouterDocument($courrierDecision);
        }

        $courrierDecision->setCorps($corpsCourrier);
        $courrierDecision = $this->imprimanteCourrier->imprimerLettreDecision($dossier, $courrierDecision)->setOriginalFilename("Lettre de décision - dossier {$dossier->getReference()}");

        $this->em->persist($courrierDecision);
        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse([
            // TODO https://symfony.com/doc/current/serializer/custom_normalizer.html
            'document' => $this->normalizer->normalize($courrierDecision, 'json', ['agent:detail']),
        ], Response::HTTP_OK);
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

        $document = (new Document())
            ->setFilename($filename)
            ->setOriginalFilename($file->getClientOriginalName())
            ->setSize($file->getSize())
            ->setType(DocumentType::TYPE_COURRIER_MINISTERE)
            ->setMime($file->getMimeType());

        $this->em->persist($document);
        $this->em->flush();

        $dossier->ajouterDocument($document);
        $dossier->changerStatut($dossier->getEtatDossier()->estAccepte() ? EtatDossierType::DOSSIER_OK_A_APPROUVER : EtatDossierType::DOSSIER_KO_REJETE, agent: $this->getAgent());

        $this->dossierRepository->save($dossier);

        $eventDispatcher->dispatch(new DossierDecideEvent($dossier));

        return new JsonResponse([
            'documents' => [
                DocumentType::TYPE_COURRIER_MINISTERE->value => $this->normalizer->normalize($document, 'json', ['agent:detail']),
            ],
            'etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
        ], Response::HTTP_OK);
    }

    #[IsGranted(Agent::ROLE_AGENT_VALIDATEUR)]
    #[Route('/dossier/{id}/envoyer.json', name: 'agent_redacteur_envoyer_dossier', methods: ['POST'])]
    public function envoyer(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        $agent = $this->getAgent();

        if ($dossier->estAccepte()) {
            $dossier
                ->changerStatut(EtatDossierType::DOSSIER_OK_A_APPROUVER, agent: $agent);
        } else {
            $dossier
                ->changerStatut(EtatDossierType::DOSSIER_KO_REJETE, agent: $agent);
        }

        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
        ], Response::HTTP_OK);
    }

    #[Route('/dossiers.json', name: 'agent_redacteur_dossiers_json', methods: ['GET'])]
    public function dossiersJson(Request $request, NormalizerInterface $normalizer): Response
    {
        return new JsonResponse(
            $normalizer->normalize(
                $this->dossierRepository->rechercheDossiers(
                    $request->query->has('e') ?
                        array_map(fn ($e) => EtatDossierType::fromSlug($e), self::extraireCritereRecherche($request, 'e')) :
                        [
                            // EtatDossierType::DOSSIER_CLOTURE,
                            // EtatDossierType::DOSSIER_A_FINALISER,
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
                ),
                'json', ['groups' => 'agent:liste']
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
