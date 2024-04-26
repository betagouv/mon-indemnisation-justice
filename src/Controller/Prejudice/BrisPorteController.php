<?php

namespace App\Controller\Prejudice;

use App\Entity\BrisPorte;
use App\Entity\Categorie;
use App\Entity\User;
use App\Service\Breadcrumb\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_REQUERANT)]
#[Route('/bris-de-porte')]
class BrisPorteController extends AbstractController
{
    public function __construct(
      private Breadcrumb $breadcrumb
    )
    {

    }

    #[Route('/ajouter-un-bris-de-porte', name: 'app_bris_porte_add', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function add(EntityManagerInterface $em): Response
    {
      $brisPorte = $em->getRepository(BrisPorte::class)->newInstance($this->getUser());
      return $this->redirectToRoute('app_bris_porte_edit',['id' => $brisPorte->getId()]);
    }

    #[IsGranted('edit', subject: 'brisPorte')]
    #[Route('/declarer-un-bris-de-porte/{id}', name: 'app_bris_porte_edit', methods: ['GET'], options: ['expose' => true])]
    public function edit(BrisPorte $brisPorte): Response
    {
      $breadcrumb = $this->breadcrumb;
      $breadcrumb->add('homepage.title','app_homepage');
      $breadcrumb->add('requerant.homepage.title','app_requerant_homepage');
      $breadcrumb->add('sinistre.declare_bris_porte.title', null);

      return $this->render('prejudice/declare_bris_porte.html.twig', [
          'breadcrumb' => $breadcrumb,
          'brisPorte' => $brisPorte
      ]);
    }
}
