<?php

namespace App\Controller\Requerant;

use App\Entity\BrisPorte;
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

    #[Route('/ajouter-un-bris-de-porte', name: 'app_bris_porte_add', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function add(EntityManagerInterface $em): Response
    {
        $requerant = $this->getRequerant();
        $brisPorte = $em->getRepository(BrisPorte::class)->newInstance($requerant);

        if (
            null !== ($testEligibilite = $requerant->getTestEligibilite()) &&
            ($dateOperationPJ = \DateTimeImmutable::createFromFormat('Y-m-d', @$testEligibilite['dateOperationPJ']))
        ) {
            if (isset($testEligibilite['dateOperationPJ'])) {
                $brisPorte->setDateOperationPJ($dateOperationPJ);
            }
            $brisPorte->setNumeroPV(@$testEligibilite['numeroPV']);
            $brisPorte->setNumeroParquet(@$testEligibilite['numeroParquet']);
            if (isset($testEligibilite['isErreurPorte'])) {
                $brisPorte->setIsErreurPorte(true);
            } elseif (isset($testEligibilite['estVise'])) {
                $brisPorte->setIsErreurPorte(!$testEligibilite['estVise'] && !$testEligibilite['estRecherche']);
            }

            if (isset($testEligibilite['estProprietaire'])) {
                $brisPorte->setQualiteRequerant($testEligibilite['estProprietaire'] ? QualiteRequerant::PRO : QualiteRequerant::LOC);
            }

            $serviceEnqueteur = $brisPorte->getServiceEnqueteur();
            $serviceEnqueteur->setNumeroPV($brisPorte->getNumeroPV());
            $serviceEnqueteur->setNumeroParquet($brisPorte->getNumeroParquet());

            $requerant->setTestEligibilite(null);
            $em->persist($requerant);
        }

        $em->persist($brisPorte);
        $em->flush();

        return $this->redirectToRoute('app_bris_porte_edit', ['id' => $brisPorte->getId()]);
    }

    #[IsGranted('edit', subject: 'brisPorte')]
    #[Route('/declarer-un-bris-de-porte/{id}', name: 'app_bris_porte_edit', methods: ['GET'], options: ['expose' => true])]
    public function edit(BrisPorte $brisPorte): Response
    {
        return $this->render('prejudice/declare_bris_porte.html.twig', [
            'brisPorte' => $brisPorte,
        ]);
    }

    #[Route('/passage-a-l-etat-constitue/{id}', name: 'app_requerant_update_statut_to_constitue', methods: ['GET'], options: ['expose' => true])]
    public function redirection(BrisPorte $brisPorte, Mailer $mailer, DocumentManager $documentManager): RedirectResponse
    {
        $requerant = $this->getRequerant();
        $brisPorte->setDeclare();
        $this->entityManager->persist($brisPorte);
        $this->entityManager->flush();

        $mailer
           ->toRequerant($requerant)
           ->subject('Votre déclaration de bris de porte a bien été pris en compte')
           ->htmlTemplate('email/bris_porte_dossier_constitue.html.twig', [
               'brisPorte' => $brisPorte,
               'requerant' => $requerant,
           ])
           ->send()
        ;

        $this->eventDispatcher->dispatch(new BrisPorteConstitueEvent($brisPorte));

        return $this->redirectToRoute('requerant_home_index');
    }
}
