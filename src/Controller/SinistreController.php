<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sinistre')]
class SinistreController extends AbstractController
{
    #[Route('/declarer-un-bris-de-porte', name: 'app_declare_bris_porte', options: ['expose' => true])]
    public function declare_bris_porte(): Response
    {
        return $this->render('sinistre/declare_bris_porte.html.twig', [
            'controller_name' => 'SinistreController',
        ]);
    }
}
