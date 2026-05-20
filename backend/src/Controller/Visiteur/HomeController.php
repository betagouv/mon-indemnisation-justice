<?php

namespace MonIndemnisationJustice\Controller\Visiteur;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/deni-de-justice/tester-mon-eligibilite', name: 'visiteur_deni_de_justice_index')]
    #[Route('/deni-de-justice/tester-mon-eligibilite/{extra}', name: 'visiteur_deni_de_justice_react', requirements: ['extra' => '.*'])]
    #[Route('/dysfonctionnement/tester-mon-eligibilite', name: 'visiteur_dysfonctionnement_index')]
    #[Route('/dysfonctionnement/tester-mon-eligibilite/{extra}', name: 'visiteur_dysfonctionnement_react', requirements: ['extra' => '.*'])]
    public function index(): Response
    {
        return $this->render('visiteur/visiteur.html.twig');
    }
}
