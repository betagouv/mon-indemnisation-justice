<?php

namespace App\Controller\Requerant;

use App\Entity\BrisPorte;
use App\Entity\GeoDepartement;
use App\Entity\QualiteRequerant;
use App\Entity\Requerant;
use App\Event\BrisPorteConstitueEvent;
use App\Service\DocumentManager;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
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
        protected readonly EntityManagerInterface $entityManager,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[IsGranted('edit', subject: 'brisPorte')]
    #[Route('/declarer-un-bris-de-porte/{id}', name: 'app_bris_porte_edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(BrisPorte $brisPorte): Response
    {
        return $this->render('prejudice/declare_bris_porte.html.twig', [
            'brisPorte' => $brisPorte,
        ]);
    }

    #[Route('/passage-a-l-etat-constitue/{id}', name: 'app_requerant_update_statut_to_constitue', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function redirection(BrisPorte $brisPorte, Mailer $mailer, DocumentManager $documentManager): RedirectResponse
    {
        $requerant = $this->getRequerant();
        $brisPorte->setDeclare();
        $this->entityManager->persist($brisPorte);
        $this->entityManager->flush();

        $mailer
           ->toRequerant($requerant)
           ->subject('Votre déclaration de bris de porte a bien été prise en compte')
           ->htmlTemplate('email/bris_porte_dossier_constitue.html.twig', [
               'dossier' => $brisPorte,
           ])
           ->send()
        ;

        $this->eventDispatcher->dispatch(new BrisPorteConstitueEvent($brisPorte));

        return $this->redirectToRoute('requerant_home_index');
    }
}
