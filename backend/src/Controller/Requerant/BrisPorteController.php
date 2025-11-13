<?php

namespace MonIndemnisationJustice\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use MonIndemnisationJustice\Repository\GeoPaysRepository;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Service\ImprimanteCourrier;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment;

#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/requerant/bris-de-porte')]
class BrisPorteController extends RequerantController
{
    public function __construct(
        protected readonly BrisPorteRepository $brisPorteRepository,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly GeoPaysRepository $geoPaysRepository,
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
        // A supprimer
        protected readonly EntityManagerInterface $em,
        protected readonly Mailer $mailer,
        protected readonly Environment $twig,
        protected readonly ImprimanteCourrier $imprimanteCourrier,
        protected readonly DocumentManager $documentManager,
    ) {}

    #[Route('/declarer-un-bris-de-porte/{id}', name: 'app_bris_porte_edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(
        #[MapEntity(id: 'id')]
        BrisPorte $dossier
    ): Response {
        if ($dossier->getRequerant() !== $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        if ($dossier->estSigne()) {
            return $this->redirectToRoute('requerant_dossier_consulter_decision');
        }

        return $this->render('requerant/dossier/declare_bris_porte.html.twig', [
            'dossier' => $dossier,
            'pays' => $this->geoPaysRepository->getListeTriee(),
        ]);
    }

    #[Route('/passage-a-l-etat-constitue/{id}', name: 'app_requerant_update_statut_to_constitue', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function redirection(#[MapEntity(id: 'id')] BrisPorte $brisPorte): RedirectResponse
    {
        $requerant = $this->getRequerant();

        if (EtatDossierType::DOSSIER_A_FINALISER === $brisPorte->getEtatDossier()->getEtat()) {
            $brisPorte
                ->changerStatut(EtatDossierType::DOSSIER_A_ATTRIBUER, requerant: true)
            ;
            $this->brisPorteRepository->save($brisPorte);

            $this->addFlash('dossier', [
                'dossier' => $brisPorte,
            ]);
        } else {
            $this->brisPorteRepository->save($brisPorte);
        }

        return $this->redirectToRoute('requerant_home_index');
    }

    #[Route('/{id}/consulter-la-decision', name: 'requerant_dossier_consulter_decision', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function consulterDecision(#[MapEntity(id: 'id')] BrisPorte $dossier, NormalizerInterface $normalizer): Response
    {
        if (!$dossier->estSigne()) {
            return $this->redirectToRoute('app_bris_porte_edit', ['id' => $dossier->getId()]);
        }

        return $this->render('requerant/dossier/decision.html.twig', [
            'react' => [
                'dossier' => $normalizer->normalize($dossier, 'json', ['groups' => 'requerant:detail']),
            ],
        ]);
    }

    #[Route('/{id}/accepter-la-decision.json', name: 'requerant_dossier_accepter_decision', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function accepterDecision(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request, NormalizerInterface $normalizer): Response
    {
        if (EtatDossierType::DOSSIER_OK_A_APPROUVER == !$dossier->getEtatDossier()->getEtat()->value) {
            return new JsonResponse([
                'erreur' => "Ce dossier n'est pas en attente d'approbation",
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('fichierSigne');
        if (null === $file) {
            return new JsonResponse([
                'erreur' => "Une erreur est survenue lors du téléversement du ficher. Veuillez ré-essayer et rapprochez-vous de l'équipe Mon Indemnisation Justice si le problème persiste",
            ], Response::HTTP_BAD_REQUEST);
        }

        $acceptation = $this->documentManager->ajouterFichierTeleverse($dossier, $file, DocumentType::TYPE_COURRIER_REQUERANT);

        $dossier->changerStatut(EtatDossierType::DOSSIER_OK_A_VERIFIER);

        $this->documentManager->genererArretePaiement($dossier);

        $this->em->persist($dossier);
        $this->em->flush();

        if (null !== $dossier->getRedacteur()) {
            // Envoi du courriel de notification au rédacteur
            $this->mailer
                ->toAgent($dossier->getRedacteur())
                ->subject(sprintf("Dossier %s: %s a approuvé l'indemnisation", $dossier->getReference(), $dossier->getRequerant()->estFeminin() ? 'la requérante' : 'le requérant'))
                ->htmlTemplate('email/agent_indemnisation_approuvee.twig', [
                    'dossier' => $dossier,
                    'agent' => $dossier->getRedacteur(),
                ])
                ->send()
            ;
        }

        return new JsonResponse([
            'etat' => $normalizer->normalize($dossier->getEtatDossier(), 'json', ['requerant:detail']),
            'document' => $normalizer->normalize($acceptation, 'json', ['groups' => 'requerant:detail']),
        ], Response::HTTP_OK);
    }
}
