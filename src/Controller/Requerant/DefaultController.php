<?php

namespace App\Controller\Requerant;

use App\Service\Breadcrumb\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/requerant/accueil', name: 'app_requerant_homepage')]
    public function index(Breadcrumb $breadcrumb): Response
    {
        $breadcrumb->add('homepage.title','app_homepage');
        $breadcrumb->add('requerant.homepage.title',null);
        return $this->render('requerant/default/index.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
