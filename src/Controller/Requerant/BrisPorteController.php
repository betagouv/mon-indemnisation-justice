<?php

namespace MonIndemnisationJustice\Controller\Requerant;

use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Event\DossierConstitueEvent;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use MonIndemnisationJustice\Repository\GeoPaysRepository;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/requerant/bris-de-porte')]
class BrisPorteController extends RequerantController
{
    public function __construct(
        protected readonly BrisPorteRepository $brisPorteRepository,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly GeoPaysRepository $geoPaysRepository,
    ) {
    }

    #[Route('/declarer-un-bris-de-porte/{id}', name: 'app_bris_porte_edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(
        #[MapEntity(id: 'id')] BrisPorte $dossier): Response
    {
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
    public function redirection(#[MapEntity(id: 'id')] BrisPorte $brisPorte, Mailer $mailer): RedirectResponse
    {
        $requerant = $this->getRequerant();

        if (EtatDossierType::DOSSIER_A_FINALISER === $brisPorte->getEtatDossier()->getEtat()) {
            $brisPorte->setDeclare();
            $this->brisPorteRepository->save($brisPorte);

            $mailer
               ->toRequerant($requerant)
               ->subject('Votre déclaration de bris de porte a bien été prise en compte')
               ->htmlTemplate('email/bris_porte_dossier_constitue.html.twig', [
                   'dossier' => $brisPorte,
               ])
               ->send()
            ;

            $this->eventDispatcher->dispatch(new DossierConstitueEvent($brisPorte));

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
}
