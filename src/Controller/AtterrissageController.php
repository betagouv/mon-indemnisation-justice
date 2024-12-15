<?php

namespace App\Controller;

use App\Entity\BrisPorte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/atterrissage')]
class AtterrissageController extends AbstractController
{
    public const SESSION_KEY = 'qrCodeBrisDePorte';

    #[Route('/bris-de-porte', name: 'atterrissage_bris_de_porte', methods: ['GET'])]
    public function brisDePorte(Request $request): Response
    {
        $request->getSession()->set(self::SESSION_KEY, true);

        return $this->redirectToRoute('app_homepage');
    }
}
