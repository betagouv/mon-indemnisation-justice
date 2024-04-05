<?php

namespace App\Controller\Requerant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/requerant/accueil', name: 'app_requerant_default')]
    public function index(): Response
    {
        return $this->render('requerant/default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}
