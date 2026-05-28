<?php

namespace MonIndemnisationJustice\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PublicController extends AbstractController
{
    #[Route('/dysfonctionnement/tester-mon-eligibilite/{extra}', name: 'public_dysfonctionnement_react', requirements: ['extra' => '.*'], env: ['dev', 'test', 'ci', 'develop'])]
    public function index(): Response
    {
        return $this->render('public/public.html.twig');
    }
}
