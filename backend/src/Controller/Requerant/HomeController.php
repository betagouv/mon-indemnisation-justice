<?php

namespace MonIndemnisationJustice\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
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
        // Suppression du contexte de session lié à l'inscription, s'il y en a un
        $request->getSession()->remove(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION);

        $requerant = $this->getRequerant();
        $navigation = $requerant->getNavigation();
        // Temporaire : le temps que les sessions expirent, faire passer l'`id` du test d'éligibilité en session en
        // priorité à celui de la navigation requérant
        $idTestEligibilite = $request->getSession()->get(PublicBrisPorteController::CLEF_SESSION_TEST_ELIGIBILITE, $navigation->idTestEligibilite);
        $testEligibilite = $idTestEligibilite ? $em->find(TestEligibilite::class, $idTestEligibilite) : null;

        /** @var BrisPorte $dossier */
        $dossier = null;

        if ($testEligibilite) {
            if (!$requerant->getDossiers()->exists(fn (int $indice, BrisPorte $dossier) => $dossier->getTestEligibilite()->id === $testEligibilite)) {
                $dossier = (new BrisPorte())
                    ->setRequerant($requerant)
                    ->setQualiteRequerant($testEligibilite->rapportAuLogement)
                    ->setTestEligibilite($testEligibilite)
                ;
            }
        } else {
            $declaration = $navigation->idDeclaration ? $em->find(DeclarationErreurOperationnelle::class, $navigation->idDeclaration) : null;

            if (!$requerant->getDossiers()->exists(fn (int $indice, BrisPorte $dossier) => $dossier->getDeclarationFDO()->getId() === $declaration->getId())) {
                $dossier = (new BrisPorte())
                    ->setRequerant($requerant)
                    ->setDeclarationFDO($declaration)
                    ->setDateOperationPJ($declaration->getDateOperation())
                    ->setAdresse($declaration->getAdresse())
                ;
                $em->persist($dossier);
            }
        }

        // Supprimer les données liées au test d'éligibilité
        // TODO supprimer une fois que la pré-inscription en session et la navigation requérant ont pris le pas
        $request->getSession()->remove(PublicBrisPorteController::CLEF_SESSION_TEST_ELIGIBILITE);

        if (null !== $dossier) {
            $em->persist($dossier);
            $em->flush();

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
