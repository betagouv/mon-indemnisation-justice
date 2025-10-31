<?php

namespace MonIndemnisationJustice\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\Adresse;
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

                $em->persist($dossier);
                $em->flush();
            }
        } else {
            $declaration = $navigation->idDeclaration ? $em->find(DeclarationErreurOperationnelle::class, $navigation->idDeclaration) : null;

            if ($declaration && !$requerant->getDossiers()->exists(fn (int $indice, BrisPorte $dossier) => $dossier->getDeclarationFDO()?->getId() === $declaration->getId())) {
                $dossier = (new BrisPorte())
                    ->setRequerant($requerant)
                    ->setDeclarationFDO($declaration)
                    ->setDateOperationPJ($declaration->getDateOperation())
                    // On recrée une nouvelle adresse pour conserver les données des FDO et pouvoir plus tard comparer et arbitrer
                    ->setAdresse(
                        (new Adresse())
                            ->setLigne1($declaration->getAdresse()->getLigne1())
                            ->setLigne2($declaration->getAdresse()->getLigne2())
                            ->setCodePostal($declaration->getAdresse()->getCodePostal())
                            ->setLocalite($declaration->getAdresse()->getLocalite())
                    )
                ;
                $em->persist($dossier);
                $em->flush();
            } else {
                // Rediriger vers un dossier à finaliser en priorité, s'il y en a un
                $dossier = $requerant->getDossiers()->findFirst(fn (int $indice, BrisPorte $dossier) => !$dossier->estDepose());
            }
        }

        // Supprimer les données liées au test d'éligibilité
        // TODO supprimer une fois que la pré-inscription en session et la navigation requérant ont pris le pas
        $request->getSession()->remove(PublicBrisPorteController::CLEF_SESSION_TEST_ELIGIBILITE);

        if (null !== $dossier) {
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
