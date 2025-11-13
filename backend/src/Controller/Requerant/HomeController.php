<?php

namespace MonIndemnisationJustice\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Requerant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/requerant')]
class HomeController extends RequerantController
{
    #[Route('', name: 'requerant_home_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Suppression du contexte de session lié à l'inscription, s'il y en a un
        $request->getSession()->remove(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION);

        $requerant = $this->getRequerant();

        // Rediriger vers un dossier à finaliser en priorité, s'il y en a un
        if (null !== ($dossier = $requerant->getDossiers()->findFirst(fn (int $indice, BrisPorte $dossier) => !$dossier->estDepose()))) {
            return $this->redirectToRoute('app_bris_porte_edit', ['id' => $dossier->getId()]);
        }

        return $this->redirectToRoute('requerant_home_dossiers');
    }

    #[Route('/mes-demandes', name: 'requerant_home_dossiers')]
    public function mesDemandes(Request $request, EntityManagerInterface $em): Response
    {
        $requerant = $this->getRequerant();

        return $this->render('requerant/default/index.html.twig', [
            'dossiers' => $requerant->getDossiers(),
        ]);
    }
}
