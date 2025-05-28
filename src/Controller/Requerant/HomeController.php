<?php

namespace MonIndemnisationJustice\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\QualiteRequerant;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;
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
        $requerant = $this->getRequerant();

        if ($request->getSession()->has(PublicBrisPorteController::SESSION_CONTEXT_KEY)) {
            // Créer le dossier ici avant de virer les infos du test d'éligibilité
            $id = $request->getSession()->get(PublicBrisPorteController::SESSION_CONTEXT_KEY, null);
            if ($id) {
                try {
                    $testEligibilite = $em->find(TestEligibilite::class, $id);

                    $dossier = (new BrisPorte())
                        ->setRequerant($requerant)
                        ->setQualiteRequerant($testEligibilite->estProprietaire ? QualiteRequerant::PRO : QualiteRequerant::LOC)
                        ->setTestEligibilite($testEligibilite)
                    ;
                    $em->persist($dossier);
                    $em->flush();
                } catch (ORMException $exception) {
                    // TODO log peut-être
                }
            }

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
