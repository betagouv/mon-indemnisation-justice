<?php

namespace App\Controller\Prejudice;

use App\Entity\BrisPorte;
use App\Entity\Categorie;
use App\Service\Breadcrumb\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bris-de-porte')]
class BrisPorteController extends AbstractController
{
    #[Route('/ajouter-un-bris-de-porte', name: 'app_bris_porte_add', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function add(Breadcrumb $breadcrumb, EntityManagerInterface $em): Response
    {
        $breadcrumb->add('homepage.title','app_homepage');
        $breadcrumb->add('requerant.homepage.title','app_requerant_homepage');
        $breadcrumb->add('sinistre.declare_bris_porte.title', null);

        $user = $this->getUser();
        /** @var Categorie $categorie */
        $categorie = $em->getRepository(Categorie::class)->findOneBy(['mnemo' => Categorie::MNEMO_BRIS_PORTE]);

        /** @var BrisPorte $brisPorte */
        $brisPorte = new BrisPorte();
        $brisPorte->setRequerant($user);
        $brisPorte->setCategorie($categorie);
        $brisPorte->setDateDeclaration(new \DateTime());
        $em->persist($brisPorte);
        $em->flush();

        return $this->render('prejudice/declare_bris_porte.html.twig', [
            'breadcrumb' => $breadcrumb,
            'brisPorte' => $brisPorte
        ]);
    }
}
