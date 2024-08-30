<?php

namespace App\Controller\Requerant;

use App\Entity\BrisPorte;
use App\Entity\Requerant;
use App\Service\Breadcrumb\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/requerant')]
class DefaultController extends AbstractController
{
    public function __construct(
      private Breadcrumb $breadcrumb,
      private EntityManagerInterface $em
    ) { }

    #[Route('/accueil', name: 'app_requerant_homepage')]
    public function index(Request $request): Response
    {
        $breadcrumb = $this->breadcrumb;
        $em = $this->em;
        $session = $request->getSession();
        /** @var array $testEligibilite */
        $testEligibilite = $session->get('test_eligibilite',[]);
        /** @var ?string $type */
        $type = $testEligibilite['type']??null;
        switch($type) {
          case 'BRI':
            return $this->redirectToRoute('app_bris_porte_add');
          default:
        }

        $breadcrumb->add('homepage.title','app_homepage');
        $breadcrumb->add('requerant.homepage.title',null);
        $brisPortes = $em
          ->getRepository(BrisPorte::class)
          ->findBy(['requerant' => $this->getUser()])
        ;

        return $this->render('requerant/default/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'brisPortes' => $brisPortes
        ]);
    }
}
