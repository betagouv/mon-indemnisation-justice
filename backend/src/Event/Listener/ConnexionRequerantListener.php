<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class ConnexionRequerantListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {}

    public function onSecurityInteractiveLogin(LoginSuccessEvent $event): void
    {
        $requerant = $event->getUser();

        if (!$requerant instanceof Requerant) {
            return;
        }

        $request = $event->getRequest();
        $request->getSession()->get(BrisPorteController::CLEF_SESSION_PREINSCRIPTION);

        $navigation = $requerant->getNavigation();
        // Temporaire : le temps que les sessions expirent, faire passer l'`id` du test d'éligibilité en session en
        // priorité à celui de la navigation requérant
        $idTestEligibilite = $request->getSession()->get(PublicBrisPorteController::CLEF_SESSION_TEST_ELIGIBILITE, $navigation->idTestEligibilite);
        $testEligibilite = $idTestEligibilite ? $this->em->find(TestEligibilite::class, $idTestEligibilite) : null;

        if ($testEligibilite) {
            if (!$requerant->getDossiers()->exists(fn (int $indice, BrisPorte $dossier) => $dossier->getTestEligibilite()->id === $testEligibilite)) {
                $dossier = (new BrisPorte())
                    ->setRequerant($requerant)
                    ->setQualiteRequerant($testEligibilite->rapportAuLogement)
                    ->setTestEligibilite($testEligibilite)
                ;
                $requerant->setNavigation(null);

                $this->em->persist($requerant);
                $this->em->persist($dossier);
                $this->em->flush();
            }
        } else {
            $declaration = $navigation->idDeclaration ? $this->em->find(DeclarationErreurOperationnelle::class, $navigation->idDeclaration) : null;

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

                $requerant->setNavigation(null);

                $this->em->persist($requerant);
                $this->em->persist($dossier);
                $this->em->flush();
            }
        }

        // Suppression des données liées au test d'éligibilité
        // TODO supprimer une fois que la pré-inscription en session et la navigation requérant ont pris le pas
        $request->getSession()->remove(PublicBrisPorteController::CLEF_SESSION_TEST_ELIGIBILITE);
        // Suppression du contexte de session lié à l'inscription, s'il y en a un
        $request->getSession()->remove(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onSecurityInteractiveLogin',
        ];
    }
}
