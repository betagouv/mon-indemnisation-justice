<?php

namespace MonIndemnisationJustice\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
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

    #[Route('/mentions-legales', name: 'public_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('mentions-legales.html.twig');
    }

    #[Route('/politique-de-confidentialite', name: 'public_politique_de_confidentialite')]
    public function politiqueDeConfidentialite(): Response
    {
        return $this->render('politique-de-confidentialite.html.twig');
    }

    #[Route('/conditions-generales-d-utilisation', name: 'public_cgu')]
    public function cgu(): Response
    {
        return $this->render('cgu.html.twig');
    }
}
