<?php

namespace App\Controller\Requerant;

use App\Entity\Requerant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Controller\BrisPorteController as PublicBrisPorteController;

#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/requerant')]
class HomeController extends RequerantController
{
    #[Route('', name: 'requerant_home_index')]
    public function index(Request $request): Response
    {
        $requerant = $this->getRequerant();

        if ($request->getSession()->has(PublicBrisPorteController::SESSION_CONTEXT_KEY)) {
            $request->getSession()->remove(PublicBrisPorteController::SESSION_CONTEXT_KEY);
        }

        $dossier = $requerant->getDernierDossier();
        if (null !== $dossier && !$dossier?->estConstitue()) {
            return $this->redirectToRoute('app_bris_porte_edit', ['id' => $dossier->getId()]);
        }

        return $this->render('requerant/default/index.html.twig', [
            'dossiers' => $requerant->getDossiers(),
        ]);
    }
}
