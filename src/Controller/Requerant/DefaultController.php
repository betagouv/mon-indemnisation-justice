<?php

namespace App\Controller\Requerant;

use App\Entity\BrisPorte;
use App\Service\Breadcrumb\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/requerant/accueil', name: 'app_requerant_homepage')]
    public function index(Breadcrumb $breadcrumb, EntityManagerInterface $em): Response
    {
        $breadcrumb->add('homepage.title','app_homepage');
        $breadcrumb->add('requerant.homepage.title',null);
        $brisPortes = $em
          ->getRepository(BrisPorte::class)
          ->findBy(['requerant' => $this->getUser()])
        ;
        return $this->render('requerant/default/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'brisPortes' => $brisPortes
        ]);
    }
}
