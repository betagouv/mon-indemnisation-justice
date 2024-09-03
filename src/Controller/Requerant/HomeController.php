<?php

namespace App\Controller\Requerant;

use App\Entity\BrisPorte;
use App\Entity\Requerant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/requerant')]
class HomeController extends AbstractController
{
    public function __construct(
      private EntityManagerInterface $em
    ) { }

    #[Route('/', name: 'requerant_home_index')]
    public function index(Request $request): Response
    {
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

        $brisPortes = $this->em
          ->getRepository(BrisPorte::class)
          ->findBy(['requerant' => $this->getUser()])
        ;

        return $this->render('requerant/default/index.html.twig', [
            'brisPortes' => $brisPortes
        ]);
    }
}
