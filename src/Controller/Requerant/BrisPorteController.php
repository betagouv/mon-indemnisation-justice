<?php

namespace App\Controller\Requerant;

use App\Entity\BrisPorte;
use App\Entity\EtatDossierType;
use App\Entity\Requerant;
use App\Event\BrisPorteConstitueEvent;
use App\Repository\BrisPorteRepository;
use App\Repository\GeoPaysRepository;
use App\Service\Mailer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    #[IsGranted('edit', subject: 'brisPorte')]
    #[Route('/declarer-un-bris-de-porte/{id}', name: 'app_bris_porte_edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(BrisPorte $brisPorte): Response
    {
        return $this->render('prejudice/declare_bris_porte.html.twig', [
            'brisPorte' => $brisPorte,
            'pays' => $this->geoPaysRepository->getListeTriee(),
        ]);
    }

    #[Route('/passage-a-l-etat-constitue/{id}', name: 'app_requerant_update_statut_to_constitue', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function redirection(BrisPorte $brisPorte, Mailer $mailer): RedirectResponse
    {
        $requerant = $this->getRequerant();

        if (EtatDossierType::DOSSIER_INITIE === $brisPorte->getEtatDossier()->getEtat()) {
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

            $this->eventDispatcher->dispatch(new BrisPorteConstitueEvent($brisPorte));

            $this->addFlash('dossier', [
                'dossier' => $brisPorte,
            ]);
        } else {
            $this->brisPorteRepository->save($brisPorte);
        }

        return $this->redirectToRoute('requerant_home_index');
    }
}
