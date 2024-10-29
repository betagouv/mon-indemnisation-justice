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
    public function index(): Response
    {
        return $this->render('index.html.twig', [
        ]);
    }

    #[Route('/qui-sommes-nous', name: 'app_qui_sommes_nous', options: ['expose' => true])]
    public function quiSommesNous(): Response
    {
        return $this->render('qui-sommes-nous.html.twig');
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

        return $this->render('suivi-dossier.html.twig', [
            'raccourci' => $raccourci,
            'statuts' => $statuts,
        ]);
    }

    /*
    #[Route('/conditions-generales-d-utilisation', name: 'app_cgu', options: ['expose' => true])]
    public function cgu(): Response
    {
        return $this->render('cgu.html.twig');
    }
    */
}
