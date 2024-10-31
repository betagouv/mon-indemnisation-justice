<?php

namespace App\Controller\Requerant;

use App\Entity\Requerant;
use App\Repository\BrisPorteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/requerant')]
class HomeController extends RequerantController
{
    public function __construct(
        protected readonly BrisPorteRepository $brisPorteRepository)
    {
    }

    #[Route('', name: 'requerant_home_index')]
    public function index(Request $request): Response
    {
        $requerant = $this->getRequerant();

        if ($requerant->getTestEligibilite()) {
            return $this->redirectToRoute('app_bris_porte_add');
        }

        return $this->render('requerant/default/index.html.twig', [
            'dossiers' => $this->brisPorteRepository->getForRequerant($requerant),
        ]);
    }
}
