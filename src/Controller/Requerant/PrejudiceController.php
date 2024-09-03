<?php

namespace App\Controller\Requerant;

use App\Controller\Prejudice\UserInterface;
use App\Entity\BrisPorte;
use App\Entity\Requerant;
use App\Entity\Statut;
use App\Repository\StatutRepository;
use App\Service\Mailer\BasicMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/requerant')]
class PrejudiceController extends AbstractController
{
    public function __construct(
        private BasicMailer $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private StatutRepository $statutRepository,
        protected readonly string $emailFrom,
        protected readonly string $emailFromLabel,
        protected readonly string $baseUrl,
    ) {
    }

    #[Route('/bris-de-porte/tester-mon-eligibilite', name: 'app_bris_porte_test_eligibilite', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function testEligibilite(): Response
    {
        return $this->render('prejudice/bris_porte/test_eligibilite.html.twig');
    }

    #[IsGranted(Requerant::ROLE_REQUERANT)]
    #[Route('/passage-a-l-etat-rejete/{id}', name: 'app_redacteur_update_statut_to_rejet', methods: ['GET'], options: ['expose' => true])]
    public function checkRejet(BrisPorte $brisPorte): JsonResponse
    {
        $user = $this->getUser();
        $this->statutRepository->addEvent($brisPorte, $user, Statut::CODE_REJETE);

        return new JsonResponse(['success' => true]);
    }

    #[IsGranted(Requerant::ROLE_REQUERANT)]
    #[Route('/passage-a-l-etat-signe/{id}', name: 'app_redacteur_update_statut_to_sign', methods: ['GET'], options: ['expose' => true])]
    public function checkSign(BrisPorte $brisPorte): JsonResponse
    {
        /** @var BasicMailer $mailer */
        $mailer = $this->mailer;
        /** @var UserInterface $user */
        $user = $this->getUser();
        /** @var Statut $lastStatut */
        $lastStatut = $brisPorte->getLastStatut();
        /** @var UserInterface $requerant */
        $requerant = $brisPorte->getRequerant();

        $mailer
           ->from($this->emailFrom, $this->emailFromLabel)
           ->to($requerant->getEmail())
           ->subject($this->translator->trans('bris_porte.edit.email.title_sign'))
           ->htmlTemplate('requerant/email/confirmation_passage_etat_signe.html.twig', [
               'mail' => $requerant->getEmail(),
               'url' => $this->baseUrl,
               'nomComplet' => $requerant->getNomComplet(),
               'reference' => $brisPorte->getReference(),
           ])
           ->send(user: $requerant)
        ;
        switch ($lastStatut->getCode()) {
            case Statut::CODE_REJETE:
                $this->statutRepository->addEvent($brisPorte, $user, Statut::CODE_SIGNATURE_REJETEE);

                return new JsonResponse(['success' => true]);
            case Statut::CODE_VALIDE:
                $this->statutRepository->addEvent($brisPorte, $user, Statut::CODE_SIGNATURE_VALIDEE);

                return new JsonResponse(['success' => true]);
            default:
        }

        return new JsonResponse(['success' => false]);
    }

    #[IsGranted(Requerant::ROLE_REQUERANT)]
    #[Route('/passage-a-l-etat-valide/{id}', name: 'app_redacteur_update_statut_to_valide', methods: ['GET'], options: ['expose' => true])]
    public function checkValide(BrisPorte $brisPorte): JsonResponse
    {
        $user = $this->getUser();
        $this->statutRepository->addEvent($brisPorte, $user, Statut::CODE_VALIDE);

        return new JsonResponse(['success' => true]);
    }
}
