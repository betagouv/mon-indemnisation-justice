<?php

namespace App\Controller;

use App\Entity\BasePrejudice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'app_homepage')]
    #[Route('/declarer-un-prejudice', name: 'app_category', options: ['expose' => true])]
    public function category(): Response
    {
        return $this->render('homepage/category.html.twig', [
        ]);
    }

    #[Route('/qui-sommes-nous', name: 'app_qui_sommes_nous', options: ['expose' => true])]
    public function index(): Response
    {
        return $this->render('homepage/index.html.twig');
    }

    #[Route('/suivi-de-mon-dossier', name: 'app_suivi_mon_dossier', options: ['expose' => true])]
    public function suiviDossier(Request $request): Response
    {
        /** @var string $raccourci */
        $raccourci = $request->get('raccourci') ?? '';

        $submittedToken = $request->getPayload()->get('_csrf_token');
        $statuts = [];
        if ($this->isCsrfTokenValid('authenticate', $submittedToken)) {
            $prejudice = $this
              ->em
              ->getRepository(BasePrejudice::class)
              ->findOneBy(['raccourci' => $raccourci])
            ;
            if (null !== $prejudice) {
                $tmp = $prejudice->getStatuts();
                foreach ($tmp as $statut) {
                    $statuts[] = [$statut->getDate()->format('d/m/Y H:i'), $statut->getLibelle()];
                }
            }
        }

        return $this->render('homepage/suivi_dossier.html.twig', [
            'raccourci' => $raccourci,
            'statuts' => $statuts,
        ]);
    }

    #[Route('/conditions-generales-d-utilisation', name: 'app_cgu', options: ['expose' => true])]
    public function cgu(): Response
    {
        return $this->render('homepage/cgu.html.twig');
    }

    #[Route('/bris-de-porte/tester-mon-eligibilite', name: 'home_test_eligibilite_bris_porte', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function testEligibiliteBrisDePorte(): Response
    {
        return $this->render('prejudice/bris_porte/test_eligibilite.html.twig');
    }
}
