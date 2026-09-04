<?php

namespace MonIndemnisationJustice\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\EtatDossierOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\PieceJointeOutput;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\DossierRepository;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Service\DossierManager;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
#[Route('/agent/redacteur')]
class DossierController extends AgentController
{
    public function __construct(
        protected readonly DossierRepository $dossierRepository,
        protected readonly AgentRepository $agentRepository,
        protected readonly DossierManager $dossierManager,
        protected readonly DocumentManager $documentManager,
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
        protected readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/', name: 'app_agent_redacteur_accueil')]
    public function index(): Response
    {
        return $this->redirectToRoute('agent_redacteur_dossiers');
    }

    #[Route('/dossiers', name: 'agent_redacteur_dossiers')]
    public function dossiers(Request $request): Response
    {
        /** @var Agent $agent */
        $agent = $request->getUser();
        $this->logger->warning("Consultation de l'ancienne route de recherche de dossier", [
            'agent' => $agent?->getId(),
        ]);

        return $this->redirectToRoute('agent_fip6_react', ['extra' => 'dossiers']);
    }

    #[Route('/dossier/{id}', name: 'agent_redacteur_consulter_dossier')]
    public function consulterDossier(#[MapEntity(id: 'id')] Dossier $dossier, Request $request): Response
    {
        /** @var Agent $agent */
        $agent = $request->getUser();
        $this->logger->warning("Consultation de l'ancienne route de consultation de dossier", [
            'agent' => $agent?->getId(),
        ]);

        // Renvoyer vers la nouvelle page de consultation de dossier, désormais gérée par React
        return $this->redirectToRoute('agent_fip6_react', ['extra' => "dossier/{$dossier->getId()}"]);
    }

    // TODO déplacer dans une route API dédiée
    #[IsGranted(
        attribute: new Expression('user.instruit(subject["dossier"])'),
        subject: [
            'dossier' => new Expression('args["dossier"]'),
        ]
    )]
    #[Route('/dossier/{id}/arrete-paiement/valider.json', name: 'agent_redacteur_valider_arrete_paiement_dossier', methods: ['POST'])]
    public function validerArretePaiementDossier(
        #[MapEntity(id: 'id')]
        Dossier $dossier,
    ): Response {
        if (null === $dossier->getDocumentParType(DocumentType::TYPE_ARRETE_PAIEMENT)) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $dossier->changerStatut(EtatDossierType::DOSSIER_OK_VERIFIE, agent: $this->getAgent());

        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse(['etat' => $this->normalizer->normalize(EtatDossierOutput::depuisEtatDossier($dossier->getEtatDossier()), 'json', ['agent:detail'])]);
    }

    // TODO déplacer dans une route API dédiée
    #[IsGranted(Agent::ROLE_AGENT_VALIDATEUR)]
    #[Route('/dossier/{id}/arrete-paiement/signer.json', name: 'agent_redacteur_signer_arrete_paiement', methods: ['POST'])]
    public function signerArretePaiement(#[MapEntity(id: 'id')] Dossier $dossier, Request $request): Response
    {
        if (EtatDossierType::DOSSIER_OK_VERIFIE !== $dossier->getEtatDossier()->getEtat()) {
            return new JsonResponse(['error' => "Cet dossier n'est pas à signer"], Response::HTTP_BAD_REQUEST);
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('fichierSigne');
        $document = $this->documentManager->ajouterFichierTeleverse($dossier, $file, DocumentType::TYPE_ARRETE_PAIEMENT, estAjoutRequerant: false);

        $dossier->changerStatut(EtatDossierType::DOSSIER_OK_A_INDEMNISER, agent: $this->getAgent(), contexte: array_merge(
            $request->getPayload()->has('montantIndemnisation') ? ['montantIndemnisation' => floatval($request->getPayload()->get('montantIndemnisation'))] : [],
            $request->getPayload()->has('motifRejet') ? ['motifRejet' => $request->getPayload()->getString('motifRejet')] : [],
        ));

        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize(EtatDossierOutput::depuisEtatDossier($dossier->getEtatDossier()), 'json'),
            'document' => $this->normalizer->normalize(PieceJointeOutput::depuisDocument($document), 'json'),
        ], Response::HTTP_OK);
    }
}
