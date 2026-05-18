<?php

namespace MonIndemnisationJustice\Controller\Visiteur;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/visiteur')]
class HomeController extends AbstractController
{
    #[Route('', name: 'visiteur_home_index')]
    #[Route('/{extra?}', name: 'visiteur_react', requirements: ['extra' => '.*'])]
    public function index(): Response
    {
        return $this->render('visiteur/visiteur.html.twig');
    }
}
