<?php

namespace App\Controller\Prejudice;

use App\Entity\BrisPorte;
use App\Entity\Categorie;
use App\Entity\Statut;
use App\Entity\User;
use App\Repository\StatutRepository;
use App\Service\Version\Version;
use App\Service\Breadcrumb\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use FOPG\Component\UtilsBundle\Env\Env;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\Mailer\BasicMailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class PrejudiceController extends AbstractController
{
    public function __construct(
      private BasicMailer $mailer,
      private UrlGeneratorInterface $urlGenerator,
      private TranslatorInterface $translator,
      private Version $version,
      private Breadcrumb $breadcrumb,
      private StatutRepository $statutRepository
    )
    {

    }

    #[Route('/bris-de-porte/tester-mon-eligibilite', name: 'app_bris_porte_test_eligibilite', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function testEligibilite(): Response
    {
      $breadcrumb = $this->breadcrumb;
      $breadcrumb->add('homepage.title','app_homepage');
      $breadcrumb->add('bris_porte.test_eligibilite.title',null);

      return $this->render('prejudice/bris_porte/test_eligibilite.html.twig', [
          'breadcrumb' => $breadcrumb,
          'version' => $this->version,
      ]);
    }

    #[IsGranted(new Expression('is_granted("'.User::ROLE_CHEF_PRECONTENTIEUX.'") or is_granted("'.User::ROLE_REDACTEUR_PRECONTENTIEUX.'")'))]
    #[Route('/passage-a-l-etat-rejete/{id}', name: 'app_redacteur_update_statut_to_rejet', methods: ['GET'], options: ['expose' => true])]
    public function checkRejet(BrisPorte $brisPorte): JsonResponse
    {
      $user = $this->getUser();
      $this->statutRepository->addEvent($brisPorte, $user, Statut::CODE_REJETE);
      return new JsonResponse(['success' => true]);
    }

    #[IsGranted(User::ROLE_CHEF_PRECONTENTIEUX)]
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
      /** @var string $urlTracking */
      $urlTracking = $this->urlGenerator->generate(
        'app_tracking',
        ['id'=> $requerant->getId(),'md5' => md5($requerant->getEmail())],
        UrlGeneratorInterface::ABSOLUTE_URL
      );

      $mailer
         ->from(Env::get('EMAIL_FROM'), Env::get('EMAIL_FROM_LABEL'))
         ->to($requerant->getEmail())
         ->subject($this->translator->trans('bris_porte.edit.email.title_sign'))
         ->htmlTemplate('requerant/email/confirmation_passage_etat_signe.html.twig',[
           'mail' => $requerant->getEmail(),
           'url' => Env::get('BASE_URL'),
           'nomComplet' => $requerant->getNomComplet(),
           'reference' => $brisPorte->getReference(),
           'urlTracking' => $urlTracking
         ])
         ->send(user: $requerant)
       ;
      switch($lastStatut->getCode()) {
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

    #[IsGranted(new Expression('is_granted("'.User::ROLE_CHEF_PRECONTENTIEUX.'") or is_granted("'.User::ROLE_REDACTEUR_PRECONTENTIEUX.'")'))]
    #[Route('/passage-a-l-etat-valide/{id}', name: 'app_redacteur_update_statut_to_valide', methods: ['GET'], options: ['expose' => true])]
    public function checkValide(BrisPorte $brisPorte): JsonResponse
    {
      $user = $this->getUser();
      $this->statutRepository->addEvent($brisPorte, $user, Statut::CODE_VALIDE);
      return new JsonResponse(['success' => true]);
    }
}
