<?php

namespace App\Controller;

use App\Service\Breadcrumb\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mes-prejudices')]
class PrejudiceController extends AbstractController
{
    #[Route('/declarer-un-bris-de-porte', name: 'app_declare_bris_porte', options: ['expose' => true])]
    public function declare_bris_porte(Breadcrumb $breadcrumb): Response
    {
        return $this->render('prejudice/declare_bris_porte.html.twig', [
            'breadcrumb' => $breadcrumb,
        ]);
    }
}
