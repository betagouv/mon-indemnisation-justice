<?php

namespace MonIndemnisationJustice\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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

#[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
#[Route('/agent/redacteur')]
class DossierController extends AgentController
{
    public function __construct(
        protected readonly BrisPorteRepository    $dossierRepository,
        protected readonly AgentRepository        $agentRepository,
        protected readonly DossierManager         $dossierManager,
        protected readonly DocumentManager        $documentManager,
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface    $normalizer,
    )
    {
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
                    'roles' => $this->getAgent()->getRoles(),
                ],
                'redacteurs' => $normalizer->normalize($this->agentRepository->getRedacteurs(), 'json', ['groups' => 'agent:resume']),
            ],
        ]);
    }

    #[Route('/dossier/{id}', name: 'agent_redacteur_consulter_dossier')]
    public function consulterDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, NormalizerInterface $normalizer): Response
    {
        return $this->render('agent/dossier/consulter_bris_porte.html.twig', [
            'titre' => 'Traitement du bris de porte ' . $dossier->getReference(),
            'react' => [
                'agent' => [
                    'id' => $this->getAgent()->getId(),
                    'roles' => $this->getAgent()->getRoles(),
                ],
                'dossier' => $normalizer->normalize($dossier, 'json', ['groups' => 'agent:detail']),
                'redacteurs' => $normalizer->normalize($this->agentRepository->getRedacteurs(), 'json', ['groups' => 'agent:resume']),
            ],
        ]);
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

        if (null === $file?->getPathname()) {
            throw new BadRequestException('Impossible de lire le contenu de la pièce jointe');
        }

        $document = $this->documentManager->ajouterFichierTeleverse($dossier, $file, $type);

        $this->em->persist($document);
        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse($this->normalizer->normalize($document, 'json', ['agent:detail']), Response::HTTP_OK);
    }

    #[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
    #[Route('/dossier/{id}/decider.json', name: 'agent_redacteur_decider_accepter_dossier', methods: ['POST'])]
    public function decider(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request, NormalizerInterface $normalizer): Response
    {
        $agent = $this->getAgent();

        if ($agent !== $dossier->getRedacteur()) {
            return new JsonResponse(['error' => "Vous n'êtes pas attribué à l'instruction de ce dossier"], Response::HTTP_UNAUTHORIZED);
        }

        $this->dossierManager->avancer(
            $dossier,
            $agent,
            contexte: $request->getPayload()->has('montantIndemnisation') ? ['montantIndemnisation' => floatval($request->getPayload()->get('montantIndemnisation'))] : null,
        );

        return new JsonResponse([
            'etat' => $normalizer->normalize($dossier->getEtatDossier(), 'json', ['agent:detail']),
        ], Response::HTTP_OK);
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
    #[Route('/dossier/{id}/arrete-paiement/valider.json', name: 'agent_redacteur_valider_arrete_paiement_dossier', methods: ['POST'])]
    public function validerArretePaiementDossier(
        #[MapEntity(id: 'id')]
        BrisPorte $dossier,
    ): Response
    {
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

        $document = $dossier->getCourrierDecision();

        if ($request->files->has('courrier')) {
            /** @var UploadedFile $file */
            $file = $request->files->get('courrier');

            if (null === $file?->getPathname()) {
                throw new BadRequestException('Impossible de lire le contenu de la pièce jointe');
            }

            $document = $this->documentManager->ajouterFichierTeleverse($dossier, $file, DocumentType::TYPE_COURRIER_MINISTERE);
        }

        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'document' => $this->normalizer->normalize($document, 'json', ['agent:detail']),
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
        $document = $this->documentManager->ajouterFichierTeleverse($dossier, $file, DocumentType::TYPE_ARRETE_PAIEMENT);

        $dossier->changerStatut(EtatDossierType::DOSSIER_OK_A_INDEMNISER, agent: $this->getAgent(), contexte: array_merge(
            $request->getPayload()->has('montantIndemnisation') ? ['montantIndemnisation' => floatval($request->getPayload()->get('montantIndemnisation'))] : [],
            $request->getPayload()->has('motifRejet') ? ['motifRejet' => $request->getPayload()->getString('motifRejet')] : [],
        ));

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

        $this->dossierManager->avancer($dossier, $agent, contexte: array_merge(
            $request->getPayload()->has('montantIndemnisation') ? ['montantIndemnisation' => floatval($request->getPayload()->get('montantIndemnisation'))] : [],
            $request->getPayload()->has('motifRejet') ? ['motifRejet' => $request->getPayload()->get('motifRejet')] : [],
        ));

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
                ? array_map(fn($e) => EtatDossierType::fromSlug($e), self::extraireCritereRecherche($request, 'e'))
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
                    fn($a) => is_numeric($a)
                ),
            ]),
            filtres: self::extraireCritereRecherche($request, 'r'),
            nonAttribue: in_array('_', self::extraireCritereRecherche($request, 'a'))
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
        $zipName = tempnam(sys_get_temp_dir(), "zip_dossier_{$dossier->getId()}");

        if (true !== $zip->open($zipName, \ZipArchive::CREATE)) {
            throw new \RuntimeException('Cannot open ' . $zipName);
        }

        /** @var Document $document */
        foreach ($dossier->getDocumentsATransmettre()->toArray() as $document) {
            $zip->addFromString(preg_replace('#/#', '', $document->getOriginalFilename()), $this->documentManager->getContenuTexte($document));
        }

        $zip->close();

        return (new BinaryFileResponse($zipName, headers: [
            'Content-Type' => 'application/zip',
            'Content-Length' => filesize($zipName),
        ]))
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, preg_replace('/\//', '', "Dossier {$dossier->getReference()}.zip"));
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
            fn($v) => !empty($v)
        );
    }
}
