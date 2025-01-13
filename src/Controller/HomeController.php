<?php

namespace MonIndemnisationJustice\Controller;

use MonIndemnisationJustice\Entity\BrisPorte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/', name: 'app_homepage')]
    #[Route('/declarer-un-prejudice', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
        ]);
    }

    #[Route('/qui-sommes-nous', name: 'app_qui_sommes_nous')]
    public function quiSommesNous(): Response
    {
        return $this->render('qui-sommes-nous.html.twig');
    }

    #[Route('/suivi-de-mon-dossier', name: 'app_suivi_mon_dossier')]
    public function suiviDossier(Request $request): Response
    {
        /** @var string $raccourci */
        $raccourci = $request->get('raccourci') ?? '';

        $dossier = null;
        if ($this->isCsrfTokenValid('suiviDeDossier', $request->getPayload()->get('_csrf_token'))) {
            $dossier = $this
              ->em
              ->getRepository(BrisPorte::class)
              ->findOneBy(['raccourci' => $raccourci]);
        }

        return $this->render('suivi-dossier.html.twig', [
            'raccourci' => $raccourci,
            'dossier' => $dossier,
        ]);
    }

    /*
    #[Route('/conditions-generales-d-utilisation', name: 'app_cgu')]
    public function cgu(): Response
    {
        return $this->render('cgu.html.twig');
    }
    */
}
