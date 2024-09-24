<?php

namespace App\Controller;

use App\Entity\BrisPorte;
use App\Entity\Requerant;
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

        $statuts = [];
        if ($this->isCsrfTokenValid('suiviDeDossier', $request->getPayload()->get('_csrf_token'))) {
            $brisPorte = $this
              ->em
              ->getRepository(BrisPorte::class)
              ->findOneBy(['raccourci' => $raccourci]);

            if (null !== $brisPorte) {
                $statuts[] = [
                    "date" => $brisPorte->getDateDeclaration()->format('d/m/Y H:i'),
                    "libelle" => $brisPorte->getLastStatut()->getLibelle(),
                ];
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

    #[Route('/bris-de-porte/tester-mon-eligibilite', name: 'home_test_eligibilite_bris_porte', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function testEligibiliteBrisDePorte(Request $request): Response
    {
        // TODO gérer des données en POST, et stocker en session les infos du test d'éligibilité
        if (Request::METHOD_POST === $request->getMethod()) {
            // TODO manipuler un objet DTO plutôt
            $testEligibilite = [
                'dateOperationPJ' => $request->request->get('dateOperationPJ'),
                'numeroPV' => $request->request->get('numeroPV'),
                'numeroParquet' => $request->request->get('numeroParquet'),
                'isErreurPorte' => (bool) $request->request->get('isErreurPorte'),
            ];

            $requerant = $this->getUser();
            if ($requerant instanceof Requerant) {
                $requerant->setTestEligibilite($testEligibilite);
                $this->em->persist($requerant);
                $this->em->flush();

                return $this->redirectToRoute('app_bris_porte_add');
            } else {
                $request->getSession()->set('testEligibilite', $testEligibilite);

                return $this->redirectToRoute('app_inscription');
            }
        }

        return $this->render('prejudice/bris_porte/test_eligibilite.html.twig');
    }
}
