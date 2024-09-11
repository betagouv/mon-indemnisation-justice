<?php

namespace App\Controller\Requerant;

use App\Entity\BrisPorte;
use App\Entity\Requerant;
use App\Service\Mailer\BasicMailer;
use Doctrine\ORM\EntityManagerInterface;
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
        protected readonly string $emailFrom,
        protected readonly string $emailFromLabel,
        protected readonly string $baseUrl,
    ) {
    }

    #[Route('/ajouter-un-bris-de-porte', name: 'app_bris_porte_add', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function add(EntityManagerInterface $em): Response
    {
        $requerant = $this->getRequerant();
        $brisPorte = $em->getRepository(BrisPorte::class)->newInstance($requerant);

        if (null !== ($testEligibilite = $requerant->getTestEligibilite())) {
            $brisPorte->setDateOperationPJ(isset($testEligibilite['dateOperationPJ']) ? \DateTimeImmutable::createFromFormat('Y-m-d', $testEligibilite['dateOperationPJ']) : null);
            $brisPorte->setNumeroPV(@$testEligibilite['numeroPV']);
            $brisPorte->setNumeroParquet(@$testEligibilite['numeroParquet']);
            $brisPorte->setIsErreurPorte(@$testEligibilite['isErreurPorte']);

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
    public function redirection(BrisPorte $brisPorte, BasicMailer $mailer): RedirectResponse
    {
        $user = $this->getRequerant();
        $brisPorte->setDeclare();
        $this->entityManager->persist($brisPorte);
        $this->entityManager->flush();

        $mailer
           ->from($this->emailFrom, $this->emailFromLabel)
           ->to($user->getEmail())
           ->subject('Précontentieux : Votre déclaration de bris de porte a bien été pris en compte')
           ->htmlTemplate('requerant/email/confirmation_passage_etat_constitue.html.twig', [
               'mail' => $user->getEmail(),
               'url' => $this->baseUrl,
               'nomComplet' => $user->getNomComplet(),
               'reference' => $brisPorte->getReference(),
               'raccourci' => $brisPorte->getRaccourci(),
           ])
           ->send(user: $user)
        ;

        return $this->redirectToRoute('requerant_home_index');
    }
}
