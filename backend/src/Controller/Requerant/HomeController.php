<?php

namespace MonIndemnisationJustice\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Usager::ROLE_REQUERANT)]
#[Route('/requerant')]
class HomeController extends AbstractController
{
    #[Route('', name: 'requerant_home_index')]
    #[Route('/{extra?}', name: 'requerant_react', requirements: ['extra' => '.*'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Suppression du contexte de session lié à l'inscription, s'il y en a un
        $request->getSession()->remove(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION);

        return $this->render('requerant/requerant.html.twig');
    }

    #[Route('/deconnexion', name: 'requerant_deconnexion', methods: ['GET'], priority: 1000)]
    public function deconnexion(Security $security): Response
    {
        return $security->logout(false);
    }

    /**
     * Cette ancienne route était accessible depuis les emails de notifications envoyés au requérant. On assure le
     * transfert tant que des usagers peuvent encore ouvrir et cliquer sur ces liens.
     */
    #[Route('/bris-de-porte/{id}/consulter-la-decision', name: 'requerant_dossier_consulter_decision', requirements: ['id' => '\d+'], methods: ['GET'], priority: 1000)]
    public function atterrissageConsulterDecision(int $id): Response
    {
        // Renvoyer vers le routeur React
        return $this->redirectToRoute('requerant_react', [
            'extra' => "dossier/bris-de-porte/$id/",
        ]);
    }
}
